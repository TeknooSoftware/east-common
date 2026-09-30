<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Form\Type\ApiKeysAuthType;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Foundation\Time\DatesService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeysAuthType::class)]
class ApiKeysAuthTypeTest extends TestCase
{
    private function buildTokenStorage(?TokenInterface $token): TokenStorageInterface
    {
        $tokenStorage = $this->createStub(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        return $tokenStorage;
    }

    private function buildTokenFor(User $user): TokenInterface
    {
        $symfonyUser = $this->createStub(AbstractUser::class);
        $symfonyUser->method('getWrappedUser')->willReturn($user);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($symfonyUser);

        return $token;
    }

    /**
     * @param array<string, array<string, mixed>> $added
     * @param array<string, array<int, callable>> $listeners
     */
    private function buildFormBuilder(array &$added, array &$listeners): FormBuilderInterface
    {
        $builder = $this->createStub(FormBuilderInterface::class);
        $builder->method('add')
            ->willReturnCallback(
                function (string $child, ?string $type = null, array $options = []) use (&$added, $builder) {
                    $added[$child] = $options;

                    return $builder;
                }
            );
        $builder->method('addEventListener')
            ->willReturnCallback(
                function (string $event, callable $listener) use (&$listeners, $builder) {
                    $listeners[$event][] = $listener;

                    return $builder;
                }
            );

        return $builder;
    }

    public function testBuildFormWithoutToken(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage(null),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $type->buildForm($builder, []);
    }

    public function testBuildFormWithNotAnAbstractUser(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createStub(UserInterface::class));

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->never())->method('add');

        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage($token),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $type->buildForm($builder, []);
    }

    public function testBuildForm(): void
    {
        $user = new User()->setAuthData([new StoredPassword()]);

        $date = new DateTimeImmutable('2024-01-01');
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->exactly(2))
            ->method('passMeTheDate')
            ->with($this->isCallable())
            ->willReturnCallback(
                static function (callable $setter) use ($date, $datesService): DatesService {
                    $setter($date);

                    return $datesService;
                }
            );

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');

        $type = new ApiKeysAuthType(
            $datesService,
            $this->buildTokenStorage($this->buildTokenFor($user)),
            $hasher,
            'ak_',
        );

        $added = [];
        $listeners = [];
        $type->buildForm($this->buildFormBuilder($added, $listeners), []);

        $this->assertArrayHasKey('name', $added);
        $this->assertArrayHasKey('expiresAt', $added);

        $callback = null;
        foreach ($added['name']['constraints'] as $constraint) {
            if ($constraint instanceof Callback) {
                $callback = $constraint->callback;
            }
        }
        $this->assertIsCallable($callback);

        //The name is not used by another key
        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('addViolation');
        $callback('name', $context, $user);

        //The name is already used by another key
        $user->addAuthData(new ApiKeysAuth([new ApiKeyToken('name')]));
        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->once())
            ->method('addViolation')
            ->with('teknoo.east.common.api_keys.error.already_exists');
        $callback('name', $context, $user);

        $this->assertCount(1, $listeners[FormEvents::POST_SUBMIT]);
        $listener = $listeners[FormEvents::POST_SUBMIT][0];

        //Nothing is done when the form is not valid
        $invalidForm = $this->createStub(FormInterface::class);
        $invalidForm->method('isValid')->willReturn(false);
        $listener(new FormEvent($invalidForm, $notCreated = new ApiKeyToken('other')));
        $this->assertSame('', $notCreated->getToken());

        //Nothing is done when the data is not a key
        $validForm = $this->createStub(FormInterface::class);
        $validForm->method('isValid')->willReturn(true);
        $listener(new FormEvent($validForm, 'not a token'));

        $apiKeys = $user->getOneAuthData(ApiKeysAuth::class);
        $this->assertInstanceOf(ApiKeysAuth::class, $apiKeys);
        $this->assertCount(1, [...$apiKeys->getTokens()]);

        //The key is created, with the expiration date already set by the form
        $apiKeyToken = new ApiKeyToken(name: 'other', expiresAt: new DateTimeImmutable('2024-02-01'));
        $listener(new FormEvent($validForm, $apiKeyToken));
        $this->assertSame('hashed', $apiKeyToken->getTokenHash());
        $this->assertMatchesRegularExpression('/^ak_[0-9a-f]{64}$/', $apiKeyToken->getToken());
        $this->assertSame($date, $apiKeyToken->getCreatedAt());
        $this->assertFalse($apiKeyToken->isExpired());
        $this->assertSame($apiKeyToken, $apiKeys->getToken('other'));

        //Keys are added to the existing ApiKeysAuth of the user, without to create a new one
        $apiKeyToken = new ApiKeyToken('third');
        $listener(new FormEvent($validForm, $apiKeyToken));
        $this->assertSame($apiKeyToken, $apiKeys->getToken('third'));
        $this->assertCount(2, [...$user->getAuthData()]);
    }

    public function testBuildFormCreatesTheApiKeysAuthOfTheUserWithoutPrefix(): void
    {
        $user = new User();

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');

        $type = new ApiKeysAuthType(
            new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01')),
            $this->buildTokenStorage($this->buildTokenFor($user)),
            $hasher,
        );

        $added = [];
        $listeners = [];
        $type->buildForm($this->buildFormBuilder($added, $listeners), []);

        $validForm = $this->createStub(FormInterface::class);
        $validForm->method('isValid')->willReturn(true);

        $apiKeyToken = new ApiKeyToken('first');
        $listeners[FormEvents::POST_SUBMIT][0](new FormEvent($validForm, $apiKeyToken));

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $apiKeyToken->getToken());
        $apiKeys = $user->getOneAuthData(ApiKeysAuth::class);
        $this->assertInstanceOf(ApiKeysAuth::class, $apiKeys);
        $this->assertSame($apiKeyToken, $apiKeys->getToken('first'));
    }

    public function testBuildFormWithAnApiKeysAuthSubclass(): void
    {
        $apiKeys = new class ([new ApiKeyToken('name')]) extends ApiKeysAuth {
        };
        $user = new User()->setAuthData([$apiKeys]);

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');

        $type = new ApiKeysAuthType(
            new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01')),
            $this->buildTokenStorage($this->buildTokenFor($user)),
            $hasher,
        );

        $added = [];
        $listeners = [];
        $type->buildForm($this->buildFormBuilder($added, $listeners), []);

        //The name is already used by a key of the subclass
        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->once())->method('addViolation');
        foreach ($added['name']['constraints'] as $constraint) {
            if ($constraint instanceof Callback) {
                ($constraint->callback)('name', $context, $user);
            }
        }

        $validForm = $this->createStub(FormInterface::class);
        $validForm->method('isValid')->willReturn(true);

        //The new key is added to the existing instance, not to a new ApiKeysAuth
        $apiKeyToken = new ApiKeyToken('other');
        $listeners[FormEvents::POST_SUBMIT][0](new FormEvent($validForm, $apiKeyToken));

        $this->assertSame($apiKeyToken, $apiKeys->getToken('other'));
        $this->assertCount(1, [...$user->getAuthData()]);
    }

    public function testConfigureOptions(): void
    {
        $type = new ApiKeysAuthType(
            $this->createStub(DatesService::class),
            $this->buildTokenStorage(null),
            $this->createStub(UserPasswordHasherInterface::class),
        );

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        $this->assertSame(ApiKeyToken::class, $resolver->resolve([])['data_class']);
    }
}

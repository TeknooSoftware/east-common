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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Form\Type\TOTPType;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPPasswordAuthenticatedUser;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(TOTPType::class)]
class TOTPTypeTest extends TestCase
{
    use FormTestTrait;

    private ?TokenStorageInterface $tokenStorage = null;

    private ?TotpAuthenticatorInterface $totpAuthenticator = null;

    private ?GoogleAuthenticatorInterface $googleAuthenticator = null;

    private function getTokenStorage(bool $stub = false): (TokenStorageInterface&Stub)|(TokenStorageInterface&MockObject)
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            if ($stub) {
                $this->tokenStorage = $this->createStub(TokenStorageInterface::class);
            } else {
                $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
            }
        }

        return $this->tokenStorage;
    }

    private function getTotpAuthenticator(bool $stub = false): (TotpAuthenticatorInterface&Stub)|(TotpAuthenticatorInterface&MockObject)
    {
        if (!$this->totpAuthenticator instanceof TotpAuthenticatorInterface) {
            if ($stub) {
                $this->totpAuthenticator = $this->createStub(TotpAuthenticatorInterface::class);
            } else {
                $this->totpAuthenticator = $this->createMock(TotpAuthenticatorInterface::class);
            }
        }

        return $this->totpAuthenticator;
    }

    private function getGoogleAuthenticator(bool $stub = false): (GoogleAuthenticatorInterface&Stub)|(GoogleAuthenticatorInterface&MockObject)
    {
        if (!$this->googleAuthenticator instanceof GoogleAuthenticatorInterface) {
            if ($stub) {
                $this->googleAuthenticator = $this->createStub(GoogleAuthenticatorInterface::class);
            } else {
                $this->googleAuthenticator = $this->createMock(GoogleAuthenticatorInterface::class);
            }
        }

        return $this->googleAuthenticator;
    }

    public function buildForm(): TOTPType
    {
        return new TOTPType(
            $this->getTokenStorage(true),
            $this->getTotpAuthenticator(true),
            $this->getGoogleAuthenticator(true),
        );
    }


    public function testConfigureOptions(): void
    {
        $this->buildForm()->configureOptions(
            $this->createStub(OptionsResolver::class)
        );

        $this->assertTrue(true);
    }

    public function testWithoutTokenInStorage(): void
    {
        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn(null);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);

        $this->assertTrue(true);
    }

    public function testWithoutUserInToken(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn(null);

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);

        $this->assertTrue(true);
    }

    public function testWithNonEastUserInToken(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createStub(UserInterface::class));

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);

        $this->assertTrue(true);
    }

    public function testWithPasswordAuthenticatedUserInToken(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createStub(PasswordAuthenticatedUser::class));

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);

        $this->assertTrue(true);
    }

    public function testWithThirdPartyAuthenticatedUserInToken(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createStub(ThirdPartyAuthenticatedUser::class));

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testWithGoogleTwoFactorInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createStub(GoogleAuthPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $this->getGoogleAuthenticator(true)
            ->method('checkCode')
            ->willReturn(false);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testWithTotpTwoFactorInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createStub(TOTPPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $this->getTotpAuthenticator(true)
            ->method('checkCode')
            ->willReturn(false);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $violation = $this->createStub(ConstraintViolationBuilderInterface::class);
                    $violation->method('atPath')->willReturnSelf();

                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->once())
                        ->method('buildViolation')
                        ->willReturn($violation);

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testWithGoogleTwoFactorInTokenWithValidCode(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createStub(GoogleAuthPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createStub(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $this->getGoogleAuthenticator(true)
            ->method('checkCode')
            ->willReturn(true);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->never())
                        ->method('buildViolation');

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testWithTotpTwoFactorInTokenWithValidCode(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createStub(TOTPPasswordAuthenticatedUser::class);
        $user->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage(true)
            ->method('getToken')
            ->willReturn($token);

        $this->getTotpAuthenticator(true)
            ->method('checkCode')
            ->willReturn(true);

        $builder = $this->createStub(FormBuilderInterface::class);

        $builder->method('add')
            ->willReturnCallback(
                function (string|FormBuilderInterface $name, ?string $type, array $options) use ($builder): Stub {
                    $context = $this->createMock(ExecutionContextInterface::class);
                    $context->expects($this->never())
                        ->method('buildViolation');

                    /** @var Callback $constraintCallback */
                    $constraintCallback = $options['constraints'][0];
                    ($constraintCallback->callback)(
                        'foo',
                        $context
                    );

                    return $builder;
                }
            );

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }
}

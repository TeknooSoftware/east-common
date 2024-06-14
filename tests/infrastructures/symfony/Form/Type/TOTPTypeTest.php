<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
 * @license     http://teknoo.software/license/mit         MIT License
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

    private function getTokenStorage(): TokenStorageInterface&MockObject
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        }

        return $this->tokenStorage;
    }

    private function getTotpAuthenticator(): TotpAuthenticatorInterface&MockObject
    {
        if (!$this->totpAuthenticator instanceof TotpAuthenticatorInterface) {
            $this->totpAuthenticator = $this->createMock(TotpAuthenticatorInterface::class);
        }

        return $this->totpAuthenticator;
    }

    private function getGoogleAuthenticator(): GoogleAuthenticatorInterface&MockObject
    {
        if (!$this->googleAuthenticator instanceof GoogleAuthenticatorInterface) {
            $this->googleAuthenticator = $this->createMock(GoogleAuthenticatorInterface::class);
        }

        return $this->googleAuthenticator;
    }

    public function buildForm(): TOTPType
    {
        return new TOTPType(
            $this->getTokenStorage(),
            $this->getTotpAuthenticator(),
            $this->getGoogleAuthenticator(),
        );
    }


    public function testConfigureOptions()
    {
        self::assertInstanceOf(
            TOTPType::class,
            $this->buildForm()->configureOptions(
                $this->createMock(OptionsResolver::class)
            )
        );
    }

    public function testWithoutTokenInStorage()
    {
        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(null);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithoutUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithNonEastUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithPasswordAuthenticatedUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->createMock(PasswordAuthenticatedUser::class));

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithThirdPartyAuthenticatedUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->createMock(ThirdPartyAuthenticatedUser::class));

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithGoogleTwoFactorInToken()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user->expects($this->any())
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getGoogleAuthenticator()->expects($this->any())
            ->method('checkCode')
            ->willReturn(false);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithTotpTwoFactorInToken()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user->expects($this->any())
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getTotpAuthenticator()->expects($this->any())
            ->method('checkCode')
            ->willReturn(false);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
                    $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
                    $violation->expects($this->any())->method('atPath')->willReturnSelf();

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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithGoogleTwoFactorInTokenWithValidCode()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user->expects($this->any())
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getGoogleAuthenticator()->expects($this->any())
            ->method('checkCode')
            ->willReturn(true);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }

    public function testWithTotpTwoFactorInTokenWithValidCode()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user->expects($this->any())
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getTotpAuthenticator()->expects($this->any())
            ->method('checkCode')
            ->willReturn(true);

        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->any())
            ->method('add')
            ->willReturnCallback(
                function ($name, $type, $options) use ($builder) {
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

        self::assertInstanceOf(
            AbstractType::class,
            $this->buildForm()->buildForm($builder, [])
        );
    }
}

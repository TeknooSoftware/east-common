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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPPasswordAuthenticatedUser;
use UnexpectedValueException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(GenerateQRCodeTextForTOTP::class)]
class GenerateQRCodeTextForTOTPTest extends TestCase
{
    private ?TokenStorageInterface $tokenStorage = null;

    private function getTokenStorage(): TokenStorageInterface&MockObject
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        }

        return $this->tokenStorage;
    }

    private function buildStep(): GenerateQRCodeTextForTOTP
    {
        return new GenerateQRCodeTextForTOTP(
            $this->getTokenStorage(),
        );
    }

    public function testWithoutTokenInStorage(): void
    {
        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
        );
    }

    public function testWithoutUserInToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn(null);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
        );
    }

    public function testWithNonEastUserInToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
        );
    }

    public function testWithPasswordAuthenticatedUserInToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createMock(PasswordAuthenticatedUser::class));

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
        );
    }

    public function testWithThirdPartyAuthenticatedUserInToken(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($this->createMock(ThirdPartyAuthenticatedUser::class));

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
        );
    }

    public function testWithGoogleTwoFactorInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $authenticator = $this->createMock(GoogleAuthenticatorInterface::class);
        $authenticator
            ->method('getQRContent')
            ->willReturn('foo');

        $this->assertInstanceOf(
            GenerateQRCodeTextForTOTP::class,
            ($this->buildStep())(
                $authenticator,
                $this->createMock(ManagerInterface::class),
            ),
        );
    }

    public function testWithTotpTwoFactorInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData($auth = new TOTPAuth());

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $user
            ->method('getTOTPAuth')
            ->willReturn($auth);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $authenticator = $this->createMock(TotpAuthenticatorInterface::class);
        $authenticator
            ->method('getQRContent')
            ->willReturn('foo');

        $this->assertInstanceOf(
            GenerateQRCodeTextForTOTP::class,
            ($this->buildStep())(
                $authenticator,
                $this->createMock(ManagerInterface::class),
            ),
        );
    }
}

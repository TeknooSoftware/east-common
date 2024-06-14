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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Recipe\Step\UserTrait;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(GenerateQRCodeTextForTOTP::class)]
#[CoversTrait(UserTrait::class)]
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

    public function testWithoutTokenInStorage()
    {
        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
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

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
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

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
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

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
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

        $this->expectException(UnexpectedValueException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ManagerInterface::class),
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

        $authenticator = $this->createMock(GoogleAuthenticatorInterface::class);
        $authenticator->expects($this->any())
            ->method('getQRContent')
            ->willReturn('foo');

        self::assertInstanceOf(
            GenerateQRCodeTextForTOTP::class,
            ($this->buildStep())(
                $authenticator,
                $this->createMock(ManagerInterface::class),
            ),
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

        $authenticator = $this->createMock(TotpAuthenticatorInterface::class);
        $authenticator->expects($this->any())
            ->method('getQRContent')
            ->willReturn('foo');

        self::assertInstanceOf(
            GenerateQRCodeTextForTOTP::class,
            ($this->buildStep())(
                $authenticator,
                $this->createMock(ManagerInterface::class),
            ),
        );
    }
}

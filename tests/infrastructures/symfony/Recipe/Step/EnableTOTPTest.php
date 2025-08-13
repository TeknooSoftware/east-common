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
use RuntimeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Recipe\Step\EnableTOTP;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(EnableTOTP::class)]
class EnableTOTPTest extends TestCase
{
    private ?SymfonyUserWriter $userWriter = null;

    private ?TokenStorageInterface $tokenStorage = null;

    private function getSymfonyUserWriter(): SymfonyUserWriter&MockObject
    {
        if (!$this->userWriter instanceof SymfonyUserWriter) {
            $this->userWriter = $this->createMock(SymfonyUserWriter::class);
        }

        return $this->userWriter;
    }

    private function getTokenStorage(): TokenStorageInterface&MockObject
    {
        if (!$this->tokenStorage instanceof TokenStorageInterface) {
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        }

        return $this->tokenStorage;
    }

    private function buildStep(): EnableTOTP
    {
        return new EnableTOTP(
            $this->getSymfonyUserWriter(),
            $this->getTokenStorage(),
        );
    }

    public function testWithoutTokenInStorage(): void
    {
        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
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

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithGoogleTwoFactorNotEnabledInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(new TOTPAuth());

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithTotpTwoFactorNotEnabledInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(new TOTPAuth());

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(TotpAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithGoogleTwoFactorEnabledInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(
            new TOTPAuth(
                provider: TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
                topSecret: 'bar',
                enabled: true
            )
        );

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);
        $user
            ->method('isGoogleAuthenticatorEnabled')
            ->willReturn(true);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->never())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithTotpTwoFactorEnabledInToken(): void
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(
            new TOTPAuth(
                provider: 'foo',
                topSecret: 'bar',
                enabled: true
            )
        );

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);
        $user
            ->method('isTotpAuthenticationEnabled')
            ->willReturn(true);

        $token = $this->createMock(TokenInterface::class);
        $token
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->never())
            ->method('save');

        $this->assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(TotpAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }
}

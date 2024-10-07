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
 * @license     http://teknoo.software/license/mit         MIT License
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

    public function testWithoutTokenInStorage()
    {
        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->expectException(RuntimeException::class);
        ($this->buildStep())(
            $this->createMock(GoogleAuthenticatorInterface::class),
            $this->createMock(ParametersBag::class),
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

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
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

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithGoogleTwoFactorNotEnabledInToken()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(new TOTPAuth());

        $user = $this->createMock(GoogleAuthPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithTotpTwoFactorNotEnabledInToken()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(new TOTPAuth());

        $user = $this->createMock(TOTPPasswordAuthenticatedUser::class);
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(TotpAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithGoogleTwoFactorEnabledInToken()
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
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);
        $user->expects($this->any())
            ->method('isGoogleAuthenticatorEnabled')
            ->willReturn(true);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->never())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(GoogleAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }

    public function testWithTotpTwoFactorEnabledInToken()
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
        $user->expects($this->any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);
        $user->expects($this->any())
            ->method('isTotpAuthenticationEnabled')
            ->willReturn(true);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects($this->any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects($this->never())
            ->method('save');

        self::assertInstanceOf(
            EnableTOTP::class,
            ($this->buildStep())(
                $this->createMock(TotpAuthenticatorInterface::class),
                $this->createMock(ParametersBag::class),
            ),
        );
    }
}

<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers      \Teknoo\East\CommonBundle\Recipe\Step\UserTrait
 * @covers      \Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP
 */
class DisableTOTPTest extends TestCase
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

    private function buildStep(): DisableTOTP
    {
        return new DisableTOTP(
            $this->getSymfonyUserWriter(),
            $this->getTokenStorage(),
        );
    }

    public function testWithoutTokenInStorage()
    {
        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        ($this->buildStep())();
    }

    public function testWithoutUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn(null);

        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        ($this->buildStep())();
    }

    public function testWithNonEastUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        ($this->buildStep())();
    }

    public function testWithPasswordAuthenticatedUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($this->createMock(PasswordAuthenticatedUser::class));

        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects(self::never())
            ->method('save');

        self::assertInstanceOf(
            DisableTOTP::class,
            ($this->buildStep())(),
        );
    }

    public function testWithThirdPartyAuthenticatedUserInToken()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($this->createMock(ThirdPartyAuthenticatedUser::class));

        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects(self::never())
            ->method('save');

        self::assertInstanceOf(
            DisableTOTP::class,
            ($this->buildStep())(),
        );
    }

    public function testWithTOTPAuthUserInToken()
    {
        $wrapperUser = new User();
        $wrapperUser->addAuthData(new TOTPAuth());

        $user = $this->createMock(ThirdPartyAuthenticatedUser::class);
        $user->expects(self::any())
            ->method('getWrappedUser')
            ->willReturn($wrapperUser);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::any())
            ->method('getUser')
            ->willReturn($user);

        $this->getTokenStorage()
            ->expects(self::any())
            ->method('getToken')
            ->willReturn($token);

        $this->getSymfonyUserWriter()
            ->expects(self::once())
            ->method('save');

        self::assertInstanceOf(
            DisableTOTP::class,
            ($this->buildStep())(),
        );
    }
}

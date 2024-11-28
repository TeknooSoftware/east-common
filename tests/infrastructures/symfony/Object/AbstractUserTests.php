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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Object;

use ArrayIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractUserTests extends TestCase
{
    private ?BaseUser $user = null;

    private ?StoredPassword $storedPassword = null;

    /**
     * @return StoredPassword|MockObject
     */
    public function getStoredPassword(): StoredPassword
    {
        if (!$this->storedPassword instanceof StoredPassword) {
            $this->storedPassword = $this->createMock(StoredPassword::class);
        }

        return $this->storedPassword;
    }

    /**
     * @return BaseUser|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->expects($this->any())->method('getAuthData')->willReturn([$this->getStoredPassword()]);
            $this->user->expects($this->any())->method('getOneAuthData')->willReturn($this->getStoredPassword());
        }

        return $this->user;
    }

    abstract public function buildObject(): AbstractUser;

    public function testGetRolesFromArray()
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        self::assertEquals(
            ['foo','bar'],
            $this->buildObject()->getRoles()
        );
    }

    public function testGetRolesFromIterable()
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(new ArrayIterator(['foo','bar']));

        self::assertEquals(
            ['foo','bar'],
            $this->buildObject()->getRoles()
        );
    }

    public function testGetUserIdentifier()
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('username');

        self::assertEquals(
            'username',
            $this->buildObject()->getUserIdentifier()
        );
    }

    public function testGetEmail()
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        self::assertEquals(
            'foo@bar',
            $this->buildObject()->getEmail()
        );
    }

    public function testGetHash()
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        self::assertEquals(
            \hash('sha256', 'foo@bar'),
            $this->buildObject()->getHash()
        );
    }

    public function testExceptionOnIsEqualToWithBadUser()
    {
        $this->expectException(TypeError::class);
        $this->buildObject()->isEqualTo(new stdClass());
    }

    public function testIsEqualToNotSameUserName()
    {
        $this->getUser()
            ->expects($this->any())
            ->method('getUserIdentifier')
            ->willReturn('myUserName');

        $user = $this->createMock(UserInterface::class);
        $user
            ->expects($this->any())
            ->method('getUserIdentifier')
            ->willReturn('notUserName');

        self::assertFalse(
            $this->buildObject()->isEqualTo($user)
        );
    }

    public function testGetWrappedUser()
    {
        self::assertInstanceOf(
            User::class,
            $this->buildObject()->getWrappedUser()
        );
    }

    public function testEraseCredentials()
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        $this->buildObject()->eraseCredentials();
    }
}

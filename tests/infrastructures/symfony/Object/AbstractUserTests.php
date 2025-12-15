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

namespace Teknoo\Tests\East\CommonBundle\Object;

use ArrayIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractUserTests extends TestCase
{
    private ?BaseUser $user = null;

    private ?StoredPassword $storedPassword = null;

    public function getStoredPassword(bool $stub = false): (StoredPassword&Stub)|(StoredPassword&MockObject)
    {
        if (!$this->storedPassword instanceof StoredPassword) {
            if ($stub) {
                $this->storedPassword = $this->createStub(StoredPassword::class);
            } else {
                $this->storedPassword = $this->createMock(StoredPassword::class);
            }
        }

        return $this->storedPassword;
    }

    public function getUser(bool $stub = false): (BaseUser&MockObject)|(BaseUser&Stub)
    {
        if (!$this->user instanceof BaseUser) {
            if ($stub) {
                $this->user = $this->createStub(BaseUser::class);
            } else {
                $this->user = $this->createMock(BaseUser::class);
            }

            $this->user->method('getAuthData')->willReturn([$this->getStoredPassword($stub)]);
            $this->user->method('getOneAuthData')->willReturn($this->getStoredPassword($stub));
        }

        return $this->user;
    }

    abstract public function buildObject(): AbstractUser;

    public function testGetRolesFromArray(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        $this->assertEquals(
            ['foo','bar'],
            $this->buildObject()->getRoles()
        );
    }

    public function testGetRolesFromIterable(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(new ArrayIterator(['foo','bar']));

        $this->assertEquals(
            ['foo','bar'],
            $this->buildObject()->getRoles()
        );
    }

    public function testGetUserIdentifier(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn('username');

        $this->assertEquals(
            'username',
            $this->buildObject()->getUserIdentifier()
        );
    }

    public function testGetEmail(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->assertEquals(
            'foo@bar',
            $this->buildObject()->getEmail()
        );
    }

    public function testGetHash(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->assertEquals(
            \hash('sha256', 'foo@bar'),
            $this->buildObject()->getHash()
        );
    }

    public function testExceptionOnIsEqualToWithBadUser(): void
    {
        $this->expectException(TypeError::class);
        $this->buildObject()->isEqualTo(new stdClass());
    }

    public function testIsEqualToNotSameUserName(): void
    {
        $this->getUser(true)
            ->method('getUserIdentifier')
            ->willReturn('myUserName');

        $user = $this->createStub(UserInterface::class);
        $user
            ->method('getUserIdentifier')
            ->willReturn('notUserName');

        $this->assertFalse(
            $this->buildObject()->isEqualTo($user)
        );
    }

    public function testGetWrappedUser(): void
    {
        $this->assertInstanceOf(
            User::class,
            $this->buildObject()->getWrappedUser()
        );
    }

    public function testEraseCredentials(): void
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        $this->buildObject()->eraseCredentials();
    }
}

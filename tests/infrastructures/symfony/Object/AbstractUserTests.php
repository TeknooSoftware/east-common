<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Object;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractUserTests extends TestCase
{
    private ?BaseUser $user = null;

    /**
     * @return BaseUser|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->expects(self::any())->method('getAuthData')->willReturn([$this->getStoredPassword()]);
        }

        return $this->user;
    }

    abstract public function buildObject(): AbstractUser;

    public function testGetRolesFromArray()
    {
        $this->getUser()
            ->expects(self::once())
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
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('getUserIdentifier')
            ->willReturn('username');

        self::assertEquals(
            'username',
            $this->buildObject()->getUserIdentifier()
        );
    }

    public function testExceptionOnIsEqualToWithBadUser()
    {
        $this->expectException(\TypeError::class);
        $this->buildObject()->isEqualTo(new \stdClass());
    }

    public function testIsEqualToNotSameUserName()
    {
        $this->getUser()
            ->expects(self::any())
            ->method('getUserIdentifier')
            ->willReturn('myUserName');

        $user = $this->createMock(UserInterface::class);
        $user
            ->expects(self::any())
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
}

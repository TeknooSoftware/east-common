<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Object;

use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\WebsiteBundle\Object\User;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Object\User
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BaseUser
     */
    private $user;

    /**
     * @return BaseUser|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);
        }

        return $this->user;
    }

    public function buildObject(): User
    {
        return new User($this->getUser());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionWithBadUser()
    {
        new User(new \stdClass());
    }

    public function testGetRoles()
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

    public function testGetPassword()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getPassword')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->buildObject()->getPassword()
        );
    }

    public function testGetSalt()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getSalt')
            ->willReturn('salt');

        self::assertEquals(
            'salt',
            $this->buildObject()->getSalt()
        );
    }

    public function testGetUsername()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn('username');

        self::assertEquals(
            'username',
            $this->buildObject()->getUsername()
        );
    }

    public function testEraseCredentials()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        self::assertInstanceOf(
            User::class,
            $this->buildObject()->eraseCredentials()
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnIsEqualToWithBadUser()
    {
        $this->buildObject()->isEqualTo(new \stdClass());
    }

    public function testIsEqualToNotSameUserName()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn('myUserName');

        $user = $this->createMock(UserInterface::class);
        $user
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn('notUserName');

        self::assertFalse(
            $this->buildObject()->isEqualTo($user)
        );
    }

    public function testIsEqualToSameUserName()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn('myUserName');

        $user = $this->createMock(UserInterface::class);
        $user
            ->expects(self::once())
            ->method('getUsername')
            ->willReturn('myUserName');

        self::assertTrue(
            $this->buildObject()->isEqualTo($user)
        );
    }
}

<?php

/**
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

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractPasswordAuthUserTest extends AbstractUserTest
{
    private ?StoredPassword $storedPassword = null;

    /**
     * @return StoredPassword|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getStoredPassword(): StoredPassword
    {
        if (!$this->storedPassword instanceof StoredPassword) {
            $this->storedPassword = $this->createMock(StoredPassword::class);
        }

        return $this->storedPassword;
    }

    public function testExceptionWithBadUser()
    {
        $this->expectException(\TypeError::class);
        new PasswordAuthenticatedUser(new \stdClass(), $this->getStoredPassword());
    }

    public function testExceptionWithBadStoredPassword()
    {
        $this->expectException(\TypeError::class);
        new PasswordAuthenticatedUser($this->getUser(), new \stdClass());
    }

    public function testGetPassword()
    {
        $this->getStoredPassword()
            ->expects(self::once())
            ->method('getHash')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->buildObject()->getPassword()
        );
    }

    public function testEraseCredentials()
    {
        $this->getStoredPassword()
            ->expects(self::once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        self::assertInstanceOf(
            PasswordAuthenticatedUserInterface::class,
            $this->buildObject()->eraseCredentials()
        );
    }

    public function testGetWrappedStoredPassword()
    {
        self::assertInstanceOf(
            StoredPassword::class,
            $this->buildObject()->getWrappedStoredPassword()
        );
    }
}

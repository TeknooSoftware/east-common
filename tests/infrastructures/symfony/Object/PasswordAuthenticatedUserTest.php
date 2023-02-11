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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\CommonBundle\Object\AbstractUser
 * @covers      \Teknoo\East\CommonBundle\Object\AbstractPasswordAuthUser
 * @covers      \Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser
 */
class PasswordAuthenticatedUserTest extends AbstractPasswordAuthUserTests
{
    private ?BaseUser $user = null;

    private ?StoredPassword $storedPassword = null;

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

    public function buildObject(): AbstractUser
    {
        return new PasswordAuthenticatedUser($this->getUser(), $this->getStoredPassword());
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

    public function testGetPasswordHasherName()
    {
        $this->getStoredPassword()
            ->expects(self::once())
            ->method('getAlgo')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->buildObject()->getPasswordHasherName()
        );
    }
}

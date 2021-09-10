<?php

/**
 * East Website.
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Object;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\WebsiteBundle\Object\AbstractUser;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Object\AbstractUser
 * @covers      \Teknoo\East\WebsiteBundle\Object\AbstractPasswordAuthUser
 * @covers      \Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser
 */
class PasswordAuthenticatedUserTest extends AbstractPasswordAuthUserTest
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

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

namespace Teknoo\Tests\East\CommonBundle\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\CommonBundle\Object\AbstractPasswordAuthUser;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PasswordAuthenticatedUser::class)]
#[CoversClass(AbstractPasswordAuthUser::class)]
#[CoversClass(AbstractUser::class)]
class PasswordAuthenticatedUserTest extends AbstractPasswordAuthUserTests
{
    private ?BaseUser $user = null;

    private ?StoredPassword $storedPassword = null;

    /**
     * @return BaseUser|MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->expects($this->any())->method('getAuthData')->willReturn([$this->getStoredPassword()]);
        }

        return $this->user;
    }

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

    public function buildObject(): AbstractUser
    {
        return new PasswordAuthenticatedUser($this->getUser(), $this->getStoredPassword());
    }

    public function testExceptionWithBadUser()
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser(new stdClass(), $this->getStoredPassword());
    }

    public function testExceptionWithBadStoredPassword()
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser($this->getUser(), new stdClass());
    }

    public function testGetPasswordHasherName()
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('getAlgo')
            ->willReturn('foo');

        self::assertEquals(
            'foo',
            $this->buildObject()->getPasswordHasherName()
        );
    }
}

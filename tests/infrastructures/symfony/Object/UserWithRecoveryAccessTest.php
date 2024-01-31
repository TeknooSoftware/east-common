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

use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Object\AbstractUser
 * @covers      \Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess
 */
class UserWithRecoveryAccessTest extends AbstractUserTests
{
    private ?BaseUser $user = null;

    private ?RecoveryAccess $recoveryAccess = null;

    /**
     * @return BaseUser|MockObject
     */
    public function getUser(): BaseUser
    {
        if (!$this->user instanceof BaseUser) {
            $this->user = $this->createMock(BaseUser::class);

            $this->user->expects(self::any())->method('getAuthData')->willReturn([$this->getRecoveryAccess()]);
        }

        return $this->user;
    }

    /**
     * @return RecoveryAccess|MockObject
     */
    public function getRecoveryAccess(): RecoveryAccess
    {
        if (!$this->recoveryAccess instanceof RecoveryAccess) {
            $this->recoveryAccess = $this->createMock(RecoveryAccess::class);
            $this->recoveryAccess->expects(self::any())->method('getParams')->willReturn(['token' => 'bar']);
        }

        return $this->recoveryAccess;
    }

    public function buildObject(): UserWithRecoveryAccess
    {
        return new UserWithRecoveryAccess(
            $this->getUser(),
            $this->getRecoveryAccess(),
            'ROLE_RECOVERY',
        );
    }

    public function testExceptionWithBadUser()
    {
        $this->expectException(TypeError::class);
        new UserWithRecoveryAccess(new stdClass(), $this->getRecoveryAccess());
    }

    public function testExceptionWithBadRecoveryAccess()
    {
        $this->expectException(TypeError::class);
        new UserWithRecoveryAccess($this->getUser(), new stdClass());
    }

    public function testGetToken()
    {
        self::assertEquals(
            'bar',
            $this->buildObject()->getToken()
        );
    }

    public function testEraseCredentials()
    {
        $this->buildObject()->eraseCredentials();
        self::assertTrue(true);
    }

    public function testGetRolesFromArray()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        $user = $this->buildObject();

        self::assertEquals(
            ['ROLE_RECOVERY'],
            $user->getRoles()
        );

        self::assertEquals(
            ['foo','bar'],
            $user->getWrappedUser()->getRoles()
        );
    }

    public function testGetRolesFromIterable()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        $user = $this->buildObject();

        self::assertEquals(
            ['ROLE_RECOVERY'],
            $user->getRoles()
        );

        self::assertEquals(
            ['foo','bar'],
            $user->getWrappedUser()->getRoles()
        );
    }

    public function testGetHash()
    {
        $this->getUser()
            ->expects(self::once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        self::assertEquals(
            \hash('sha256', 'foo@bar:bar'),
            $this->buildObject()->getHash()
        );
    }
}

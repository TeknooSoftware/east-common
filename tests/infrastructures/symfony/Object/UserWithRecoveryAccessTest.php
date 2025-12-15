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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use stdClass;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(UserWithRecoveryAccess::class)]
#[CoversClass(AbstractUser::class)]
class UserWithRecoveryAccessTest extends AbstractUserTests
{
    private ?BaseUser $user = null;

    private ?RecoveryAccess $recoveryAccess = null;

    public function getUser(bool $stub = false): (BaseUser&MockObject)|(BaseUser&Stub)
    {
        if (!$this->user instanceof BaseUser) {
            if ($stub) {
                $this->user = $this->createStub(BaseUser::class);
            } else {
                $this->user = $this->createMock(BaseUser::class);
            }

            $this->user->method('getAuthData')->willReturn([$this->getRecoveryAccess()]);
            $this->user->method('getOneAuthData')->willReturn($this->getStoredPassword(true));
        }

        return $this->user;
    }

    /**
     * @return RecoveryAccess|MockObject
     */
    public function getRecoveryAccess(): RecoveryAccess
    {
        if (!$this->recoveryAccess instanceof RecoveryAccess) {
            $this->recoveryAccess = $this->createStub(RecoveryAccess::class);
            $this->recoveryAccess->method('getParams')->willReturn(['token' => 'bar']);
        }

        return $this->recoveryAccess;
    }

    public function buildObject(): UserWithRecoveryAccess
    {
        return new UserWithRecoveryAccess(
            $this->getUser(true),
            $this->getRecoveryAccess(),
            'ROLE_RECOVERY',
        );
    }

    public function testExceptionWithBadUser(): void
    {
        $this->expectException(TypeError::class);
        new UserWithRecoveryAccess(new stdClass(), $this->getRecoveryAccess());
    }

    public function testExceptionWithBadRecoveryAccess(): void
    {
        $this->expectException(TypeError::class);
        new UserWithRecoveryAccess($this->getUser(true), new stdClass());
    }

    public function testGetToken(): void
    {
        $this->assertEquals(
            'bar',
            $this->buildObject()->getToken()
        );
    }

    #[\Override]
    public function testEraseCredentials(): void
    {
        $this->buildObject()->eraseCredentials();
        $this->assertTrue(true);
    }

    #[\Override]
    public function testGetRolesFromArray(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        $user = $this->buildObject();

        $this->assertEquals(
            ['ROLE_RECOVERY'],
            $user->getRoles()
        );

        $this->assertEquals(
            ['foo','bar'],
            $user->getWrappedUser()->getRoles()
        );
    }

    #[\Override]
    public function testGetRolesFromIterable(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getRoles')
            ->willReturn(['foo','bar']);

        $user = $this->buildObject();

        $this->assertEquals(
            ['ROLE_RECOVERY'],
            $user->getRoles()
        );

        $this->assertEquals(
            ['foo','bar'],
            $user->getWrappedUser()->getRoles()
        );
    }

    #[\Override]
    public function testGetHash(): void
    {
        $this->getUser()
            ->expects($this->once())
            ->method('getEmail')
            ->willReturn('foo@bar');

        $this->assertEquals(
            \hash('sha256', 'foo@bar:bar'),
            $this->buildObject()->getHash()
        );
    }
}

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
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\CommonBundle\Object\AbstractPasswordAuthUser;
use Teknoo\East\CommonBundle\Object\AbstractUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\Common\Object\User as BaseUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PasswordAuthenticatedUser::class)]
#[CoversClass(AbstractPasswordAuthUser::class)]
#[CoversClass(AbstractUser::class)]
class PasswordAuthenticatedUserTest extends AbstractPasswordAuthUserTests
{
    private ?BaseUser $user = null;

    private ?StoredPassword $storedPassword = null;

    #[\Override]
    public function getUser(bool $stub = false): (BaseUser&MockObject)|(BaseUser&Stub)
    {
        if (!$this->user instanceof BaseUser) {
            if ($stub) {
                $this->user = $this->createStub(BaseUser::class);
            } else {
                $this->user = $this->createMock(BaseUser::class);
            }

            $this->user->method('getAuthData')->willReturn([$this->getStoredPassword(true)]);
        }

        return $this->user;
    }

    /**
     * @return StoredPassword|MockObject
     */
    #[\Override]
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

    public function buildObject(): AbstractUser
    {
        return new PasswordAuthenticatedUser($this->getUser(true), $this->getStoredPassword(true));
    }

    #[\Override]
    public function testExceptionWithBadUser(): void
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser(new stdClass(), $this->getStoredPassword(true));
    }

    #[\Override]
    public function testExceptionWithBadStoredPassword(): void
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser($this->getUser(true), new stdClass());
    }

    public function testGetPasswordHasherName(): void
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('getAlgo')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->buildObject()->getPasswordHasherName()
        );
    }
}

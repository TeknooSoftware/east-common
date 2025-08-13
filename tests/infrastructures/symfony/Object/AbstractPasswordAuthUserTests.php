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

use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPasswordAuthUserTests extends AbstractUserTests
{
    public function testExceptionWithBadUser(): void
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser(new stdClass(), $this->getStoredPassword());
    }

    public function testExceptionWithBadStoredPassword(): void
    {
        $this->expectException(TypeError::class);
        new PasswordAuthenticatedUser($this->getUser(), new stdClass());
    }

    public function testGetPassword(): void
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('getHash')
            ->willReturn('foo');

        $this->assertEquals(
            'foo',
            $this->buildObject()->getPassword()
        );
    }

    #[\Override]
    public function testEraseCredentials(): void
    {
        $this->getStoredPassword()
            ->expects($this->once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        $this->buildObject()->eraseCredentials();
    }

    public function testGetWrappedStoredPassword(): void
    {
        $this->assertInstanceOf(
            StoredPassword::class,
            $this->buildObject()->getWrappedStoredPassword()
        );
    }
}

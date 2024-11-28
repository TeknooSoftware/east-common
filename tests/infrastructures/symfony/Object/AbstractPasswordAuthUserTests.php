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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPasswordAuthUserTests extends AbstractUserTests
{
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

    public function testGetPassword()
    {
        $this->getStoredPassword()
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('eraseCredentials')
            ->willReturnSelf();

        $this->buildObject()->eraseCredentials();
    }

    public function testGetWrappedStoredPassword()
    {
        self::assertInstanceOf(
            StoredPassword::class,
            $this->buildObject()->getWrappedStoredPassword()
        );
    }
}

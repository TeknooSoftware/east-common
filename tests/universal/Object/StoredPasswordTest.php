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

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(StoredPassword::class)]
class StoredPasswordTest extends TestCase
{
    use PopulateObjectTrait;

    public function buildObject(): StoredPassword
    {
        return new StoredPassword();
    }

    public function testGetHash(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['hash' => 'fooBar'])->getHash()
        );
    }

    public function testGetPassword(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['hash' => 'fooBar'])->getPassword()
        );
    }

    public function testSetPassword(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getHash()
        );

        $this->assertTrue($object->mustHashPassword());

        $this->assertInstanceOf(
            $object::class,
            $object->setPassword(null)
        );

        $this->assertEquals(
            'fooBar',
            $object->getHash()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->setPassword('')
        );

        $this->assertEquals(
            'fooBar',
            $object->getHash()
        );
    }

    public function testSetHashedPassword(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setHashedPassword('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getHash()
        );

        $this->assertFalse($object->mustHashPassword());

        $this->assertInstanceOf(
            $object::class,
            $object->setHashedPassword(null)
        );

        $this->assertEmpty(
            $object->getHash()
        );
    }

    public function testEraseCredentials(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getHash()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar2')
        );

        $this->assertEquals(
            'fooBar2',
            $object->getHash()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->eraseCredentials()
        );

        $this->assertEmpty($object->getHash());
    }

    public function testSetPasswordExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setPassword(new \stdClass());
    }

    public function testGetAlgo(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['algo' => 'fooBar'])->getAlgo()
        );
    }

    public function testSetAlgo(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setAlgo('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getAlgo()
        );
    }

    public function testSetSaltExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setSalt(new \stdClass());
    }
}

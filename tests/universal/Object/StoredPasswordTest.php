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

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Object\StoredPassword
 */
class StoredPasswordTest extends TestCase
{
    use PopulateObjectTrait;

    /**
     * @return StoredPassword
     */
    public function buildObject(): StoredPassword
    {
        return new StoredPassword();
    }

    public function testGetHash()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['hash' => 'fooBar'])->getHash()
        );
    }

    public function testGetPassword()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['hash' => 'fooBar'])->getPassword()
        );
    }

    public function testSetPassword()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertTrue($object->mustHashPassword());

        self::assertInstanceOf(
            $object::class,
            $object->setPassword(null)
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertInstanceOf(
            $object::class,
            $object->setPassword('')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );
    }

    public function testSetHashedPassword()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setHashedPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertFalse($object->mustHashPassword());

        self::assertInstanceOf(
            $object::class,
            $object->setHashedPassword(null)
        );

        self::assertEmpty(
            $object->getHash()
        );
    }

    public function testEraseCredentials()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertInstanceOf(
            $object::class,
            $object->setPassword('fooBar2')
        );

        self::assertEquals(
            'fooBar2',
            $object->getHash()
        );

        self::assertInstanceOf(
            $object::class,
            $object->eraseCredentials()
        );

        self::assertEmpty($object->getHash());
    }

    public function testSetPasswordExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setPassword(new \stdClass());
    }

    public function testGetAlgo()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['algo' => 'fooBar'])->getAlgo()
        );
    }

    public function testSetAlgo()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setAlgo('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getAlgo()
        );
    }

    public function testSetSaltExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setSalt(new \stdClass());
    }
}

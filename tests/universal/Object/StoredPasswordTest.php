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

namespace Teknoo\Tests\East\Website\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\Tests\East\Website\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\StoredPassword
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
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertTrue($object->mustHashPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword(null)
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertInstanceOf(
            \get_class($object),
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
            \get_class($object),
            $object->setHashedPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertFalse($object->mustHashPassword());

        self::assertInstanceOf(
            \get_class($object),
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
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getHash()
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar2')
        );

        self::assertEquals(
            'fooBar2',
            $object->getHash()
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->eraseCredentials()
        );

        self::assertEmpty($object->getHash());
    }

    public function testSetPasswordExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setPassword(new \stdClass());
    }

    public function testGetSalt()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['salt' => 'fooBar'])->getSalt()
        );
    }

    public function testSetSalt()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setSalt('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getSalt()
        );
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
            \get_class($object),
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

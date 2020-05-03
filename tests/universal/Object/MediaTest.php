<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Media
 */
class MediaTest extends TestCase
{
    use ObjectTestTrait;

    /**
     * @return Media
     */
    public function buildObject(): Media
    {
        return new class extends Media {
            public function getResource()
            {
                return null;
            }
        };
    }

    public function testGetName()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['name' => 'fooBar'])->getName()
        );
    }

    public function testSetName()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setName('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getName()
        );
    }

    public function testSetNameExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setName(new \stdClass());
    }
    
    public function testGetLength()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['length' => 123])->getLength()
        );
    }

    public function testSetLength()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setLength(123)
        );

        self::assertEquals(
            123,
            $object->getLength()
        );
    }

    public function testSetLengthExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLength(new \stdClass());
    }

    public function testGetMimeType()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['mimeType' => 'fooBar'])->getMimeType()
        );
    }

    public function testSetMimeType()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setMimeType('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getMimeType()
        );
    }

    public function testSetMimeTypeExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setMimeType(new \stdClass());
    }

    public function testGetAlternative()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['alternative' => 'fooBar'])->getAlternative()
        );
    }

    public function testSetAlternative()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setAlternative('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getAlternative()
        );
    }

    public function testSetAlternativeExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setAlternative(new \stdClass());
    }
    public function testGetFile()
    {
        $mock = $this->createMock(\MongoGridFSFile::class);
        self::assertEquals(
            $mock,
            $this->generateObjectPopulated(['file' => $mock])->getFile()
        );
    }

    public function testSetFile()
    {
        $object = $this->buildObject();
        $mock = $this->createMock(\MongoGridFSFile::class);
        self::assertInstanceOf(
            \get_class($object),
            $object->setFile($mock)
        );

        self::assertEquals(
            $mock,
            $object->getFile()
        );
    }
}

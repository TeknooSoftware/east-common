<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Object\Media
 */
class MediaTest extends TestCase
{
    use PopulateObjectTrait;

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

    public function testGetId()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['id' => 123])->getId()
        );
    }

    public function testSetId()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setId('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getId()
        );
    }

    public function testSetIdExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setId(new \stdClass());
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
            $object::class,
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
            $object::class,
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

    public function testGetMetadata()
    {
        $object = new MediaMetadata('foo', 'bar', 'world');
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['metadata' => $object])->getMetadata()
        );
    }

    public function testSetMetadata()
    {
        $mdt = new MediaMetadata('foo', 'bar', 'world');

        $object = $this->buildObject();
        self::assertInstanceOf(
            Media::class,
            $object->setMetadata($mdt)
        );

        self::assertEquals(
            $mdt,
            $object->getMetadata()
        );
    }
}

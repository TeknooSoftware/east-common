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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use Doctrine\MongoDB\GridFSFile;
use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Media
 */
class MediaTest extends \PHPUnit\Framework\TestCase
{
    use ObjectTestTrait;

    /**
     * @return Media
     */
    public function buildObject(): Media
    {
        return new Media();
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

    /**
     * @expectedException \Throwable
     */
    public function testSetNameExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetLengthExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetMimeTypeExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetAlternativeExceptionOnBadArgument()
    {
        $this->buildObject()->setAlternative(new \stdClass());
    }

    public function testGetFile()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['file' => 'fooBar'])->getFile()
        );
    }

    public function testSetFile()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setFile('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getFile()
        );
    }

    public function testSetFileWithLength()
    {
        $mongoFile = $this->createMock(\MongoGridFSFile::class);
        $mongoFile->expects($this->any())->method('getSize')->willReturn(123);
        
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setFile($mongoFile)
        );

        self::assertEquals(
            $mongoFile,
            $object->getFile()
        );

        self::assertEquals(
            123,
            $object->getLength()
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetResourceNotAvailable()
    {
        $this->generateObjectPopulated(['file' => 'fooBar'])->getResource();
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testGetResourceNotResource()
    {
        $file = $this->getMockBuilder('MongoGridFSFileMock')->setMethods(['getResource'])->getMock();
        $file->expects($this->any())->method('getResource')->willReturn('foobar');

        $this->generateObjectPopulated(['file' => $file])->getResource();
    }

    public function testGetResource()
    {
        $resource = \fopen('php://memory', 'r');
        $file = $this->getMockBuilder('MongoGridFSFileMock')->setMethods(['getResource'])->getMock();
        $file->expects($this->any())->method('getResource')->willReturn($resource);

        self::assertEquals(
            $resource,
            $this->generateObjectPopulated(['file' => $file])->getResource()
        );
    }

    public function testGetResourceFromMongo()
    {
        $resource = \fopen('php://memory', 'r');
        $file = $this->getMockBuilder('MongoGridFSFileMock')->setMethods(['getResource'])->getMock();
        $file->expects($this->any())->method('getResource')->willReturn($resource);


        $mongoFile = $this->createMock(GridFSFile::class);
        $mongoFile->expects($this->any())->method('getMongoGridFSFile')->willReturn($file);

        self::assertEquals(
            $resource,
            $this->generateObjectPopulated(['file' => $mongoFile])->getResource()
        );
    }
}

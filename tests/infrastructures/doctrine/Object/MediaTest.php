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

namespace Teknoo\Tests\East\Website\Doctrine\Object;

use Doctrine\MongoDB\GridFSFile;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\Tests\East\Website\Object\MediaTest as OriginaTest;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Object\Media
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Media
 */
class MediaTest extends OriginaTest
{
    public function buildObject(): Media
    {
        return new Media();
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

    public function testGetResourceNotAvailable()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateObjectPopulated(['file' => 'fooBar'])->getResource();
    }

    public function testGetResourceNotResource()
    {
        $this->expectException(\RuntimeException::class);
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
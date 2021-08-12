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

namespace Teknoo\Tests\East\Website\Doctrine\Writer\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\ODM\MongoDB\Repository\UploadOptions;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Website\Object\Media as OriginalMedia;
use Teknoo\East\Website\Object\MediaMetadata;
use Teknoo\East\Website\Writer\MediaWriter as OriginalWriter;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Writer\ODM\MediaWriter
 */
class MediaWriterTest extends TestCase
{
    private ?GridFSRepository $repository = null;

    private ?OriginalWriter $writer = null;

    /**
     * @return GridFSRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getGridFSRepository(): GridFSRepository
    {
        if (!$this->repository instanceof GridFSRepository) {
            $this->repository = $this->createMock(GridFSRepository::class);
        }

        return $this->repository;
    }

    /**
     * @return OriginalWriter|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getOriginalWriter(): OriginalWriter
    {
        if (!$this->writer instanceof OriginalWriter) {
            $this->writer = $this->createMock(OriginalWriter::class);
        }

        return $this->writer;
    }

    public function buildWriter(): MediaWriter
    {
        return new MediaWriter(
            $this->getGridFSRepository(),
            $this->getOriginalWriter()
        );
    }

    public function testSaveWithNonManagedMedia()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('fail');

        $media = new class extends OriginalMedia {

        };

        $this->getGridFSRepository()
            ->expects(self::never())
            ->method('uploadFromFile');

        self::assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save($media, $promise)
        );
    }

    public function testSaveWithNoMediaMetadata()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('fail');

        $this->getGridFSRepository()
            ->expects(self::never())
            ->method('uploadFromFile');

        self::assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save(new Media(), $promise)
        );
    }

    public function testSave()
    {
        $media1 = new Media();
        $media1->setLength(123);
        $media1->setName('foo');

        $metadata1 = new MediaMetadata('image/jpeg', 'foo.jpeg', 'foo', '/foo/bar');
        $media1->setMetadata($metadata1);

        $media2 = new Media();
        $media2->setId('af12');
        $media2->setLength(123);
        $media2->setName('foo');

        $metadata2 = new MediaMetadata('image/jpeg', 'foo.jpeg', 'foo');
        $media2->setMetadata($metadata2);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('fail');
        $promise->expects(self::once())->method('success')->with($media2)->willReturnSelf();

        $options = new UploadOptions();
        $options->metadata = $metadata1;
        $options->chunkSizeBytes = 123;
        $this->getGridFSRepository()
            ->expects(self::once())
            ->method('uploadFromFile')
            ->with(
                '/foo/bar',
                'foo',
                $options
            )
            ->willReturn($media2);

        self::assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save($media1, $promise)
        );
    }

    public function testRemove()
    {
        $object = $this->createMock(Media::class);
        $promise = $this->createMock(PromiseInterface::class);

        $this->getOriginalWriter()
            ->expects(self::once())
            ->method('remove')
            ->with($object, $promise)
            ->willReturnSelf();

        self::assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->remove($object, $promise)
        );
    }
}
<?php

/*
 * East Website.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\Writer\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\ODM\MongoDB\Repository\UploadOptions;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\Media as OriginalMedia;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Common\Writer\MediaWriter as OriginalWriter;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter
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

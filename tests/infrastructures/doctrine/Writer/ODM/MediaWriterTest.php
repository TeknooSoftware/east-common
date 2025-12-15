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

namespace Teknoo\Tests\East\Common\Doctrine\Writer\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\ODM\MongoDB\Repository\UploadOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(MediaWriter::class)]
class MediaWriterTest extends TestCase
{
    private ?GridFSRepository $repository = null;

    private ?OriginalWriter $writer = null;

    public function getGridFSRepository(bool $stub = false): (GridFSRepository&Stub)|(GridFSRepository&MockObject)
    {
        if (!$this->repository instanceof GridFSRepository) {
            if ($stub) {
                $this->repository = $this->createStub(GridFSRepository::class);
            } else {
                $this->repository = $this->createMock(GridFSRepository::class);
            }
        }

        return $this->repository;
    }

    public function getOriginalWriter(bool $stub = false): (OriginalWriter&Stub)|(OriginalWriter&MockObject)
    {
        if (!$this->writer instanceof OriginalWriter) {
            if ($stub) {
                $this->writer = $this->createStub(OriginalWriter::class);
            } else {
                $this->writer = $this->createMock(OriginalWriter::class);
            }
        }

        return $this->writer;
    }

    public function buildWriter(): MediaWriter
    {
        return new MediaWriter(
            $this->getGridFSRepository(true),
            $this->getOriginalWriter(true)
        );
    }

    public function testSaveWithNonManagedMedia(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('fail');

        $media = new class () extends OriginalMedia {
        };

        $this->getGridFSRepository()
            ->expects($this->never())
            ->method('uploadFromFile');

        $this->assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save($media, $promise)
        );
    }

    public function testSaveWithNoMediaMetadata(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('fail');

        $this->getGridFSRepository()
            ->expects($this->never())
            ->method('uploadFromFile');

        $this->assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save(new Media(), $promise)
        );
    }

    public function testSave(): void
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
        $promise->expects($this->never())->method('fail');
        $promise->expects($this->once())->method('success')->with($media2)->willReturnSelf();

        $options = new UploadOptions();
        $options->metadata = $metadata1;
        $options->chunkSizeBytes = 123;
        $this->getGridFSRepository()
            ->expects($this->once())
            ->method('uploadFromFile')
            ->with(
                '/foo/bar',
                'foo',
                $options
            )
            ->willReturn($media2);

        $this->assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->save($media1, $promise)
        );
    }

    public function testRemove(): void
    {
        $object = $this->createStub(Media::class);
        $promise = $this->createStub(PromiseInterface::class);

        $this->getOriginalWriter()
            ->expects($this->once())
            ->method('remove')
            ->with($object, $promise)
            ->willReturnSelf();

        $this->assertInstanceOf(
            MediaWriter::class,
            $this->buildWriter()->remove($object, $promise)
        );
    }
}

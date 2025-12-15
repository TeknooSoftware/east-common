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

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\Repository\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use MongoDB\BSON\ObjectId;
use MongoDB\GridFS\Bucket;
use MongoDB\GridFS\Exception\FileNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Doctrine\Repository\ODM\Media;

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
#[CoversClass(Media::class)]
class MediaTest extends TestCase
{
    private (DocumentManager&Stub)|(DocumentManager&MockObject)|null $dm = null;

    private (UnitOfWork&Stub)|(UnitOfWork&MockObject)|null $uow = null;

    private (ClassMetadata&Stub)|(ClassMetadata&MockObject)|null $class = null;

    public function getDocumentManagerMock(bool $stub = false): (DocumentManager&Stub)|(DocumentManager&MockObject)
    {
        if (!$this->dm instanceof DocumentManager) {
            if ($stub) {
                $this->dm = $this->createStub(DocumentManager::class);
            } else {
                $this->dm = $this->createMock(DocumentManager::class);
            }
        }

        return $this->dm;
    }

    public function getUnitOfWork(bool $stub = false): (UnitOfWork&Stub)|(UnitOfWork&MockObject)
    {
        if (!$this->uow instanceof UnitOfWork) {
            if ($stub) {
                $this->uow = $this->createStub(UnitOfWork::class);
            } else {
                $this->uow = $this->createMock(UnitOfWork::class);
            }
        }

        return $this->uow;
    }

    public function getClassMetadata(bool $stub = false): (ClassMetadata&Stub)|(ClassMetadata&MockObject)
    {
        if (!$this->class instanceof ClassMetadata) {
            if ($stub) {
                $this->class = $this->createStub(ClassMetadata::class);
                $this->class->name = 'fooBar';
            } else {
                $this->class = $this->createMock(ClassMetadata::class);
                $this->class->name = 'fooBar';
            }
        }

        return $this->class;
    }

    public function buildRepository(): Media
    {
        return new Media(
            $this->getDocumentManagerMock(true),
            $this->getUnitOfWork(true),
            $this->getClassMetadata(true)
        );
    }

    public function testOpenDownloadStreamWithLegacyId(): void
    {
        $id = 'IEdJQ4vbUO7UNyrlmjIZUoQWCW99TYPq';
        $bucket = $this->createStub(Bucket::class);
        $bucket
            ->method('openDownloadStream')
            ->with($id)
            ->willReturn(\fopen('php://memory', 'r'));

        $this->getClassMetadata(true)
            ->method('getDatabaseIdentifierValue')
            ->willReturnCallback(fn ($id): ObjectId => new ObjectId($id));

        $this->getDocumentManagerMock(true)
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        $this->assertNotEmpty(
            $this->buildRepository()->openDownloadStream($id)
        );
    }

    public function testOpenDownloadStreamWithObjectId(): void
    {
        $id = '5f0f4a76c0918d70c7759a52';
        $bucket = $this->createStub(Bucket::class);
        $bucket
            ->method('openDownloadStream')
            ->with(new ObjectId($id))
            ->willReturn(\fopen('php://memory', 'r'));

        $this->getClassMetadata(true)
            ->method('getDatabaseIdentifierValue')
            ->willReturnCallback(fn ($id): ObjectId => new ObjectId($id));

        $this->getDocumentManagerMock(true)
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        $this->assertNotEmpty(
            $this->buildRepository()->openDownloadStream($id)
        );
    }

    public function testOpenDownloadStreamWithObjectIdException(): void
    {
        $bucket = $this->createStub(Bucket::class);
        $bucket
            ->method('openDownloadStream')
            ->willThrowException(new FileNotFoundException('foo'));

        $this->getDocumentManagerMock(true)
            ->method('getDocumentBucket')
            ->willReturn($bucket);

        $this->expectException(DocumentNotFoundException::class);
        $this->assertNotEmpty(
            $this->buildRepository()->openDownloadStream('2MbSIZleD7tjslM4luOgN1ho')
        );
    }
}

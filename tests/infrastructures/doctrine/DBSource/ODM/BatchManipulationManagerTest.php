<?php

/*
 * East Common.
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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\DeletingQueryInterface;
use Teknoo\East\Common\Contracts\Query\UpdatingQueryInterface;
use Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(BatchManipulationManager::class)]
class BatchManipulationManagerTest extends TestCase
{
    private ?ManagerInterface $baseManager = null;

    private ?DocumentManager $documentManager = null;

    private function getManager(): MockObject&ManagerInterface
    {
        if (null === $this->baseManager) {
            $this->baseManager = $this->createMock(ManagerInterface::class);
        }

        return $this->baseManager;
    }

    private function getDocumentManager(): MockObject&DocumentManager
    {
        if (null === $this->documentManager) {
            $this->documentManager = $this->createMock(DocumentManager::class);
        }

        return $this->documentManager;
    }

    private function buildBatchManager(): BatchManipulationManager
    {
        return new BatchManipulationManager(
            $this->getManager(),
            $this->getDocumentManager(),
        );
    }

    public function testUpdateQuery()
    {
        $query = $this->createMock(UpdatingQueryInterface::class);
        $query->expects($this->once())
            ->method('update')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->updateQuery(
                $query,
                $this->createMock(PromiseInterface::class),
            )
        );
    }

    public function testDeleteQuery()
    {
        $query = $this->createMock(DeletingQueryInterface::class);
        $query->expects($this->once())
            ->method('delete')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->deleteQuery(
                $query,
                $this->createMock(PromiseInterface::class),
            )
        );
    }

    public function testOpenBatch()
    {
        $this->getManager()
            ->expects($this->once())
            ->method('openBatch')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->openBatch()
        );
    }

    public function testCloseBatch()
    {
        $this->getManager()
            ->expects($this->once())
            ->method('closeBatch')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->closeBatch()
        );
    }

    public function testPersist()
    {
        $this->getManager()
            ->expects($this->once())
            ->method('persist')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->persist(
                $this->createMock(ObjectInterface::class),
            )
        );
    }

    public function testRemove()
    {
        $this->getManager()
            ->expects($this->once())
            ->method('remove')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->remove(
                $this->createMock(ObjectInterface::class),
            )
        );
    }

    public function testFlush()
    {
        $this->getManager()
            ->expects($this->once())
            ->method('flush')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->flush()
        );
    }
}
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
use Teknoo\East\Common\Doctrine\Filter\ODM\SoftDeletableFilter;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

    public function testUpdateQuery(): void
    {
        $query = $this->createMock(UpdatingQueryInterface::class);
        $query->expects($this->once())
            ->method('update')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->updateQuery(
                $query,
                $this->createMock(PromiseInterface::class),
            )
        );
    }

    public function testDeleteQuery(): void
    {
        $query = $this->createMock(DeletingQueryInterface::class);
        $query->expects($this->once())
            ->method('delete')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->deleteQuery(
                $query,
                $this->createMock(PromiseInterface::class),
            )
        );
    }

    public function testOpenBatch(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('openBatch')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->openBatch()
        );
    }

    public function testCloseBatch(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('closeBatch')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->closeBatch()
        );
    }

    public function testPersist(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('persist')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->persist(
                $this->createMock(ObjectInterface::class),
            )
        );
    }

    public function testRemove(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('remove')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->remove(
                $this->createMock(ObjectInterface::class),
            )
        );
    }

    public function testFlush(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('flush')
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->flush()
        );
    }

    public function testRegisterFilter(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('registerFilter')
            ->with(SoftDeletableFilter::class, ['foo' => 'bar'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->registerFilter(SoftDeletableFilter::class, ['foo' => 'bar'])
        );
    }

    public function testEnableFilter(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('enableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilter(): void
    {
        $this->getManager()
            ->expects($this->once())
            ->method('disableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->disableFilter(SoftDeletableFilter::class)
        );
    }
}

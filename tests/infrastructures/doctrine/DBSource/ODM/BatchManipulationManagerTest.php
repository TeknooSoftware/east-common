<?php

/*
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager
 */
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
        $query->expects(self::once())
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
        $query->expects(self::once())
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

    public function testPersist()
    {
        $this->getManager()
            ->expects(self::once())
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
            ->expects(self::once())
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
            ->expects(self::once())
            ->method('flush')
            ->willReturnSelf();

        self::assertInstanceOf(
            BatchManipulationManagerInterface::class,
            $this->buildBatchManager()->flush()
        );
    }
}
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

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\DBSource\Manager\AlreadyStartedBatchException;
use Teknoo\East\Common\DBSource\Manager\NonStartedBatchException;
use Teknoo\East\Common\Doctrine\Contracts\DBSource\ConfigurationHelperInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\Manager;
use Teknoo\East\Common\Doctrine\Filter\ODM\SoftDeletableFilter;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Manager::class)]
class ManagerTest extends TestCase
{
    private (ObjectManager&MockObject)|null $objectManager = null;

    /**
     * @return ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineObjectManagerMock(): ObjectManager
    {
        if (!$this->objectManager instanceof ObjectManager) {
            $this->objectManager = $this->createMock(ObjectManager::class);
        }

        return $this->objectManager;
    }

    public function buildManager(?ConfigurationHelperInterface $configurationHelper = null): Manager
    {
        return new Manager($this->getDoctrineObjectManagerMock(), $configurationHelper);
    }

    public function testPersist(): void
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->persist($object)
        );
    }

    public function testRemove(): void
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->remove($object)
        );
    }

    public function testFlush(): void
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->flush()
        );
    }

    public function testFlushOnBatch(): void
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->never())
            ->method('flush');

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->openBatch()->flush()
        );
    }

    public function testOpenBatchException(): void
    {
        $this->expectException(AlreadyStartedBatchException::class);
        $this->buildManager()->openBatch()->openBatch();
    }

    public function testCloseBatchException(): void
    {
        $this->expectException(NonStartedBatchException::class);
        $this->buildManager()->closeBatch();
    }

    public function testOnBatch(): void
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('flush');

        $manager = $this->buildManager();
        $this->assertInstanceOf(
            Manager::class,
            $manager->openBatch()->flush()->flush()->flush()->closeBatch()
        );
    }

    public function testRegisterFilterWithoutHelper(): void
    {
        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->registerFilter(SoftDeletableFilter::class, ['foo' => 'bar'])
        );
    }

    public function testEnableFilterWithoutHelper(): void
    {
        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilterWithoutHelper(): void
    {
        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager()->disableFilter(SoftDeletableFilter::class)
        );
    }

    public function testRegisterFilterWithHelper(): void
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('registerFilter')
            ->with(SoftDeletableFilter::class, ['foo' => 'bar'])
            ->willReturnSelf();

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->registerFilter(SoftDeletableFilter::class, ['foo' => 'bar'])
        );
    }

    public function testEnableFilterWithHelper(): void
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('enableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilterWithHelper(): void
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('disableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        $this->assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->disableFilter(SoftDeletableFilter::class)
        );
    }
}

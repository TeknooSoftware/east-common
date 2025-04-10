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

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\DBSource\Manager\AlreadyStartedBatchException;
use Teknoo\East\Common\DBSource\Manager\NonStartedBatchException;
use Teknoo\East\Common\Doctrine\Contracts\DBSource\ConfigurationHelperInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\Manager;
use Teknoo\East\Common\Doctrine\Filter\ODM\SoftDeletableFilter;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Manager::class)]
class ManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

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

    public function testPersist()
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->persist($object)
        );
    }

    public function testRemove()
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->remove($object)
        );
    }

    public function testFlush()
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('flush');

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->flush()
        );
    }

    public function testFlushOnBatch()
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->never())
            ->method('flush');

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->openBatch()->flush()
        );
    }

    public function testOpenBatchException()
    {
        $this->expectException(AlreadyStartedBatchException::class);
        $this->buildManager()->openBatch()->openBatch();
    }

    public function testCloseBatchException()
    {
        $this->expectException(NonStartedBatchException::class);
        $this->buildManager()->closeBatch();
    }

    public function testOnBatch()
    {
        $this->getDoctrineObjectManagerMock()
            ->expects($this->once())
            ->method('flush');

        $manager = $this->buildManager();
        self::assertInstanceOf(
            Manager::class,
            $manager->openBatch()->flush()->flush()->flush()->closeBatch()
        );
    }

    public function testRegisterFilterWithoutHelper()
    {
        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->registerFilter(SoftDeletableFilter::class, ['foo' => 'bar'])
        );
    }

    public function testEnableFilterWithoutHelper()
    {
        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilterWithoutHelper()
    {
        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->disableFilter(SoftDeletableFilter::class)
        );
    }

    public function testRegisterFilterWithHelper()
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('registerFilter')
            ->with(SoftDeletableFilter::class, ['foo' => 'bar'])
            ->willReturnSelf();

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->registerFilter(SoftDeletableFilter::class, ['foo' => 'bar'])
        );
    }

    public function testEnableFilterWithHelper()
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('enableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilterWithHelper()
    {
        $helper = $this->createMock(ConfigurationHelperInterface::class);
        $helper->expects($this->once())
            ->method('disableFilter')
            ->with(SoftDeletableFilter::class)
            ->willReturnSelf();

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager($helper)->disableFilter(SoftDeletableFilter::class)
        );
    }
}

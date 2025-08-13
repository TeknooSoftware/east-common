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

namespace Teknoo\Tests\East\Common\Writer;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait PersistTestTrait
{
    /**
     * @var \Teknoo\East\Common\Contracts\DBSource\ManagerInterface
     */
    private $manager;

    /**
     * @var DatesService
     */
    private $datesService;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerInterface
     */
    public function getObjectManager(): ManagerInterface
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    /**
     * @return DatesService|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDatesServiceMock(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    abstract public function buildWriter(bool $preferRealDateOnUpdate = false): WriterInterface;

    /**
     * @return object
     */
    abstract public function getObject();

    public function testSave(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object));
    }

    public function testSaveWithPromiseSuccess(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects($this->any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter, $preferRealDate) use ($date) {
                        $setter($date);
                        $this->assertFalse($preferRealDate);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects($this->never())
                ->method('passMeTheDate');
        }

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object, $promise));
    }

    public function testSaveWithPromiseSuccessWithNotPreferedRealDateSetInWriterPropertyButOverloadedInCall(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects($this->any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter, $preferRealDate) use ($date) {
                        $setter($date);
                        $this->assertTrue($preferRealDate);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects($this->never())
                ->method('passMeTheDate');
        }

        $this->assertInstanceOf(
            WriterInterface::class,
            $this->buildWriter(false)->save($object, $promise, true)
        );
    }

    public function testSaveWithPromiseSuccessWithPreferedRealDateSetInWriterProperty(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects($this->any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter, $preferRealDate) use ($date) {
                        $setter($date);
                        $this->assertTrue($preferRealDate);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects($this->never())
                ->method('passMeTheDate');
        }

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter(true)->save($object, $promise));
    }

    public function testSaveWithPromiseSuccessWithPreferedRealDateSetInWriterPropertyOverloadedToFalseInCall(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects($this->any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter, $preferRealDate) use ($date) {
                        $setter($date);
                        $this->assertFalse($preferRealDate);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects($this->never())
                ->method('passMeTheDate');
        }

        $this->assertInstanceOf(
            WriterInterface::class,
            $this->buildWriter(true)->save($object, $promise, false)
        );
    }

    public function testSaveWithPromiseSuccessWithPreferedRealDateSetInWriterPropertyOverloadedToTrueInCall(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects($this->any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter, $preferRealDate) use ($date) {
                        $setter($date);
                        $this->assertTrue($preferRealDate);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects($this->never())
                ->method('passMeTheDate');
        }

        $this->assertInstanceOf(
            WriterInterface::class,
            $this->buildWriter(true)->save($object, $promise, true)
        );
    }

    public function testSaveWithPromiseFailure(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($error);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('success');

        $promise->expects($this->once())
            ->method('fail')
            ->with($error)
            ->willReturnSelf();

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object, $promise));
    }

    public function testSaveWithoutPromiseFailure(): void
    {
        $this->expectException(\Exception::class);
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('persist')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($error);

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object));
    }

    public function testRemove(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->remove($object));
    }

    public function testRemoveWithPromiseSuccess(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willReturnSelf();

        $promise->expects($this->never())
            ->method('fail');

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->remove($object, $promise));
    }

    public function testRemoveWithPromiseFailure(): void
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($error);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('success');

        $promise->expects($this->once())
            ->method('fail')
            ->with($error)
            ->willReturnSelf();

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->remove($object, $promise));
    }

    public function testRemoveWithoutPromiseFailure(): void
    {
        $this->expectException(\Exception::class);
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('remove')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($error);

        $this->assertInstanceOf(WriterInterface::class, $this->buildWriter()->remove($object));
    }
}

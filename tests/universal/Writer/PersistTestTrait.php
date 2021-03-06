<?php

/**
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Common\Writer;

use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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

    /**
     * @return WriterInterface
     */
    abstract public function buildWriter(): \Teknoo\East\Common\Contracts\Writer\WriterInterface;

    /**
     * @return object
     */
    abstract public function getObject();

    public function testSave()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush');

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->save($object));
    }

    public function testSaveWithPromiseSuccess()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('persist')
            ->with($object);

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->with($object)
            ->willReturnSelf();

        $promise->expects(self::never())
            ->method('fail');

        $date = new \DateTime('2017-01-01');

        if ($object instanceof TimestampableInterface) {
            $this->getDatesServiceMock()
                ->expects(self::any())
                ->method('passMeTheDate')
                ->willReturnCallback(
                    function ($setter) use ($date) {
                        $setter($date);

                        return $this->getDatesServiceMock();
                    }
                );
        } else {
            $this->getDatesServiceMock()
                ->expects(self::never())
                ->method('passMeTheDate');
        }

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->save($object, $promise));
    }

    public function testSaveWithPromiseFailure()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('persist')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($error);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())
            ->method('success');

        $promise->expects(self::once())
            ->method('fail')
            ->with($error)
            ->willReturnSelf();

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->save($object, $promise));
    }

    public function testSaveWithoutPromiseFailure()
    {
        $this->expectException(\Exception::class);
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('persist')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($error);

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->save($object));
    }
    
    public function testRemove()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('remove')
            ->with($object);

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush');

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->remove($object));
    }

    public function testRemoveWithPromiseSuccess()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('remove')
            ->with($object);

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())
            ->method('success')
            ->willReturnSelf();

        $promise->expects(self::never())
            ->method('fail');

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->remove($object, $promise));
    }

    public function testRemoveWithPromiseFailure()
    {
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('remove')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($error);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())
            ->method('success');

        $promise->expects(self::once())
            ->method('fail')
            ->with($error)
            ->willReturnSelf();

        self::assertInstanceOf(\Teknoo\East\Common\Contracts\Writer\WriterInterface::class, $this->buildWriter()->remove($object, $promise));
    }

    public function testRemoveWithoutPromiseFailure()
    {
        $this->expectException(\Exception::class);
        $object = $this->getObject();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('remove')
            ->with($object);

        $error = new \Exception();

        $this->getObjectManager()
            ->expects(self::once())
            ->method('flush')
            ->willThrowException($error);

        self::assertInstanceOf(WriterInterface::class, $this->buildWriter()->remove($object));
    }
}

<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Writer;

use Doctrine\Common\Persistence\ObjectManager;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait PersistTestTrait
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    public function getObjectManager(): ManagerInterface
    {
        if (!$this->manager instanceof ManagerInterface) {
            $this->manager = $this->createMock(ManagerInterface::class);
        }

        return $this->manager;
    }

    /**
     * @return WriterInterface
     */
    abstract public function buildWriter(): WriterInterface;

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

        self::assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object));
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

        self::assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object, $promise));
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

        self::assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object, $promise));
    }

    /**
     * @expectedException \Exception
     */
    public function testSaveWithoutPromiseFailure()
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

        self::assertInstanceOf(WriterInterface::class, $this->buildWriter()->save($object));
    }
}

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

namespace Teknoo\Tests\East\Website\Doctrine\DBSource;

use Doctrine\Common\Persistence\ObjectRepository;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait RepositoryTestTrait
{
    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * @return ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDoctrineObjectRepositoryMock(): ObjectRepository
    {
        if (!$this->objectRepository instanceof ObjectRepository) {
            $this->objectRepository = $this->createMock(ObjectRepository::class);
        }

        return $this->objectRepository;
    }

    /**
     * @return RepositoryInterface
     */
    abstract public function buildRepository(): RepositoryInterface;

    /**
     * @expectedException \TypeError
     */
    public function testFindBadId()
    {
        $this->buildRepository()->find(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindBadPromise()
    {
        $this->buildRepository()->find('abc', new \stdClass());
    }

    public function testFindNothing()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \DomainException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('find')
            ->with('abc')
            ->willReturn([]);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
        );
    }

    public function testFindOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('find')
            ->with('abc')
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindAllBadPromise()
    {
        $this->buildRepository()->findAll(new \stdClass());
    }

    public function testFindAll()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findAll')
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findAll($promise)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindByBadCriteria()
    {
        $this->buildRepository()->findBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindByBadPromise()
    {
        $this->buildRepository()->findBy(['foo' => 'bar'], new \stdClass());
    }

    public function testFindBy()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findBy(['foo' => 'bar'], $promise)
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindByBadOrder()
    {
        $this->buildRepository()->findBy(
            ['foo' => 'bar'],
            $this->createMock(PromiseInterface::class),
            new \stdClass()
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindOneByBadCriteria()
    {
        $this->buildRepository()->findOneBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testFindOneByBadPromise()
    {
        $this->buildRepository()->findOneBy(['foo' => 'bar'], new \stdClass());
    }

    public function testFindOneByNothing()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \DomainException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['foo' => 'bar'])
            ->willReturn([]);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByExpcetion()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \RuntimeException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['foo' => 'bar'])
            ->willThrowException(new \RuntimeException());

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }
}

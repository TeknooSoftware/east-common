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
use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\DocumentRepository;
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

    public function testFindBadId()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->find(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindBadPromise()
    {
        $this->expectException(\TypeError::class);
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

    public function testFindAllBadPromise()
    {
        $this->expectException(\TypeError::class);
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

    public function testFindByBadCriteria()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindByBadPromise()
    {
        $this->expectException(\TypeError::class);
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

    public function testFindByForMongo()
    {
        $this->objectRepository = $this->createMock(DocumentRepository::class);

        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->objectRepository
            ->expects(self::never())
            ->method('findBy');

        $query = $this->createMock(Query::class);
        $query->expects(self::once())->method('execute')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects(self::any())
            ->method('getQuery')
            ->willReturn($query);

        $this->objectRepository
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findBy(['foo' => 'bar'], $promise, ['foo'=>'ASC'], 1, 2)
        );
    }

    public function testFindByBadOrder()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(
            ['foo' => 'bar'],
            $this->createMock(PromiseInterface::class),
            new \stdClass()
        );
    }

    public function testFindOneByBadCriteria()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findOneBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindOneByBadPromise()
    {
        $this->expectException(\TypeError::class);
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

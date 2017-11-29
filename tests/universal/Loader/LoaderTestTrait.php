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

namespace Teknoo\Tests\East\Website\Loader;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\LoaderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait LoaderTestTrait
{
    /**
     * @return ObjectRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract public function getRepositoryMock(): ObjectRepository;

    /**
     * @return LoaderInterface
     */
    abstract public function buildLoader(): LoaderInterface;

    /**
     * @return object
     */
    abstract public function getEntity();

    /**
     * @expectedException \Throwable
     */
    public function testLoadBadId()
    {
        $this->buildLoader()->load(new \stdClass(), new Promise());
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadBadPromise()
    {
        $this->buildLoader()->load(['fooBar'=>true], new \stdClass());
    }

    public function testLoadNotFound()
    {
        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['fooBar'=>true, 'deletedAt'=>null])
            ->willReturn(null);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail')
            ->with($this->callback(function ($exception) {
                return $exception instanceof \DomainException;
            }));

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->load(['fooBar'=>true], $promiseMock)
        );
    }

    public function testLoadFound()
    {
        $entity = $this->getEntity();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['fooBar'=>true, 'deletedAt'=>null])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with($entity);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->load(['fooBar'=>true], $promiseMock)
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadCollectionBadId()
    {
        $this->buildLoader()->loadCollection(new \stdClass(), new Promise());
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadCollectionBadPromise()
    {
        $this->buildLoader()->loadCollection(['fooBar'=>true], new \stdClass());
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadCollectionBadOrder()
    {
        $this->buildLoader()->loadCollection(
            ['fooBar'=>true, 'deletedAt'=>null],
            $this->createMock(PromiseInterface::class),
            new \stdClass()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadCollectionBadLimit()
    {
        $this->buildLoader()->loadCollection(
            ['fooBar'=>true, 'deletedAt'=>null],
            $this->createMock(PromiseInterface::class),
            ['fooBar' => 'ASC'],
            new \stdClass()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadCollectionBadOffSet()
    {
        $this->buildLoader()->loadCollection(
            ['fooBar'=>true, 'deletedAt'=>null],
            $this->createMock(PromiseInterface::class),
            ['fooBar' => 'ASC'],
            123,
            new \stdClass()
        );
    }

    public function testLoadCollectionError()
    {
        $e = new \Exception();
        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder->expects(self::any())
            ->method('equals')
            ->with(['fooBar'=>true, 'deletedAt'=>null])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('sort')
            ->with(['fooBar' => 'ASC'])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('limit')
            ->with(123)
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('skip')
            ->with(456)
            ->willReturnSelf();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::any())
            ->method('getQuery')
            ->willThrowException($e);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())->method('fail')->with($e);

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->loadCollection(
                ['fooBar'=>true],
                $promiseMock,
                ['fooBar' => 'ASC'],
                123,
                456
            )
        );
    }

    public function testLoadCollectionNotFound()
    {
        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder->expects(self::any())
            ->method('equals')
            ->with(['fooBar'=>true, 'deletedAt'=>null])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('sort')
            ->with(['fooBar' => 'ASC'])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('limit')
            ->with(123)
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('skip')
            ->with(456)
            ->willReturnSelf();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $query = $this->createMock(Query::class);
        $query->expects(self::any())
            ->method('execute')
            ->willReturn([]);

        $queryBuilder
            ->expects(self::any())
            ->method('getQuery')
            ->willReturn($query);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with([]);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->loadCollection(
                ['fooBar'=>true],
                $promiseMock,
                ['fooBar' => 'ASC'],
                123,
                456
            )
        );
    }

    public function testLoadCollectionFound()
    {
        $entity = $this->getEntity();
        $queryBuilder = $this->createMock(Builder::class);
        $queryBuilder->expects(self::any())
            ->method('equals')
            ->with(['fooBar'=>true, 'deletedAt'=>null])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('sort')
            ->with(['fooBar' => 'ASC'])
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('limit')
            ->with(123)
            ->willReturnSelf();
        $queryBuilder->expects(self::any())
            ->method('skip')
            ->with(456)
            ->willReturnSelf();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $query = $this->createMock(Query::class);
        $query->expects(self::any())
            ->method('execute')
            ->willReturn([$entity]);

        $queryBuilder
            ->expects(self::any())
            ->method('getQuery')
            ->willReturn($query);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with([$entity]);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->loadCollection(
                ['fooBar'=>true],
                $promiseMock,
                ['fooBar' => 'ASC'],
                123,
                456
            )
        );
    }
}

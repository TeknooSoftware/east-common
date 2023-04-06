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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Loader;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\Promise;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait LoaderTestTrait
{
    /**
     * @return \Teknoo\East\Common\Contracts\DBSource\RepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    abstract public function getRepositoryMock(): RepositoryInterface;

    abstract public function buildLoader(): LoaderInterface;

    /**
     * @return object
     */
    abstract public function getEntity();

    public function testLoadBadId()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->load(new \stdClass(), new Promise());
    }

    public function testLoadBadPromise()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->load('fooBar', new \stdClass());
    }

    public function testLoadWithError()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail');

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['id'=>'fooBar', 'deletedAt'=>null], $promiseMock)
            ->willThrowException(new \Exception());

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->load('fooBar', $promiseMock)
        );
    }

    public function testLoad()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::never())->method('fail');

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['id'=>'fooBar', 'deletedAt'=>null], $promiseMock);

        self::assertInstanceOf(
            \Teknoo\East\Common\Contracts\Loader\LoaderInterface::class,
            $this->buildLoader()->load('fooBar', $promiseMock)
        );
    }

    public function testQueryBadQuery()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->query(new \stdClass(), new Promise());
    }

    public function testQueryBadPromise()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->query($this->createMock(QueryCollectionInterface::class), new \stdClass());
    }

    public function testQuery()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::never())->method('fail');

        $loader = $this->buildLoader();

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $queryMock
         */
        $queryMock = $this->createMock(QueryCollectionInterface::class);
        $queryMock->expects(self::once())
            ->method('execute')
            ->with($loader, $this->getRepositoryMock(), $promiseMock);

        self::assertInstanceOf(
            \Teknoo\East\Common\Contracts\Loader\LoaderInterface::class,
            $loader->query($queryMock, $promiseMock)
        );
    }

    public function testFetchBadFetch()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->fetch(new \stdClass(), new Promise());
    }

    public function testFetchBadPromise()
    {
        $this->expectException(\Throwable::class);
        $this->buildLoader()->fetch($this->createMock(QueryElementInterface::class), new \stdClass());
    }

    public function testFetch()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::never())->method('fail');

        $loader = $this->buildLoader();

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $fetchMock
         */
        $fetchMock = $this->createMock(QueryElementInterface::class);
        $fetchMock->expects(self::once())
            ->method('fetch')
            ->with($loader, $this->getRepositoryMock(), $promiseMock);

        self::assertInstanceOf(
            \Teknoo\East\Common\Contracts\Loader\LoaderInterface::class,
            $loader->fetch($fetchMock, $promiseMock)
        );
    }
}

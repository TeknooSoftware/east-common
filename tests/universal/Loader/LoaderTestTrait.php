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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Loader;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\QueryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait LoaderTestTrait
{
    /**
     * @return RepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    abstract public function getRepositoryMock(): RepositoryInterface;

    /**
     * @return LoaderInterface
     */
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
            LoaderInterface::class,
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
        $this->buildLoader()->query($this->createMock(QueryInterface::class), new \stdClass());
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
        $queryMock = $this->createMock(QueryInterface::class);
        $queryMock->expects(self::once())
            ->method('execute')
            ->with($loader, $this->getRepositoryMock(), $promiseMock);

        self::assertInstanceOf(
            LoaderInterface::class,
            $loader->query($queryMock, $promiseMock)
        );
    }
}

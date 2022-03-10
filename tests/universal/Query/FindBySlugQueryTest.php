<?php

/**
 * East Website.
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
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Query;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Query\Expr\NotEqual;
use Teknoo\East\Website\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\FindBySlugQuery;
use Teknoo\East\Website\Query\QueryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\FindBySlugQuery
 */
class FindBySlugQueryTest extends TestCase
{
    use QueryElementTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(bool $includedDeleted = false, mixed $object = null): QueryElementInterface
    {
        return new FindBySlugQuery('fooBarName', 'HelloWorld', $includedDeleted, $object);
    }

    public function testFetch()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'deletedAt' => null,], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithDeleted()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithNonSavedObject()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $object = $this->createMock(ObjectInterface::class);

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true, $object)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithSavedObject()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $object = $this->createMock(ObjectInterface::class);
        $object->expects(self::any())->method('getId')->willReturn('foo');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'id' => new NotEqual('foo')], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true, $object)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchFailing()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository) {
                $promise->fail(new \RuntimeException());

                return $repository;
            });

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->fetch($loader, $repository, $promise)
        );
    }
}

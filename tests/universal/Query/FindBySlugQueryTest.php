<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\Common\Query;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\FindBySlugQuery;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Query\FindBySlugQuery
 */
class FindBySlugQueryTest extends TestCase
{
    use QueryElementTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(bool $includedDeleted = false, mixed $object = null): \Teknoo\East\Common\Contracts\Query\QueryElementInterface
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
            ->with(['fooBarName' => 'HelloWorld', 'deletedAt' => null,], $this->callback(fn($pr) => $pr instanceof PromiseInterface));

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
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(fn($pr) => $pr instanceof PromiseInterface));

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

        $object = $this->createMock(IdentifiedObjectInterface::class);

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(fn($pr) => $pr instanceof PromiseInterface));

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

        $object = $this->createMock(IdentifiedObjectInterface::class);
        $object->expects(self::any())->method('getId')->willReturn('foo');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'id' => new NotEqual('foo')], $this->callback(fn($pr) => $pr instanceof PromiseInterface));

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

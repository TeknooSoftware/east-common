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

namespace Teknoo\Tests\East\Common\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\FindBySlugQuery;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FindBySlugQuery::class)]
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

    public function testFetch(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->never())->method('fail');

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'deletedAt' => null,], $this->callback(fn ($pr): bool => $pr instanceof PromiseInterface));

        $this->assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithDeleted(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->never())->method('fail');

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(fn ($pr): bool => $pr instanceof PromiseInterface));

        $this->assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithNonSavedObject(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->never())->method('fail');

        $object = $this->createMock(IdentifiedObjectInterface::class);

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(fn ($pr): bool => $pr instanceof PromiseInterface));

        $this->assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true, $object)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchWithSavedObject(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->never())->method('fail');

        $object = $this->createMock(IdentifiedObjectInterface::class);
        $object->method('getId')->willReturn('foo');

        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'id' => new NotEqual('foo')], $this->callback(fn ($pr): bool => $pr instanceof PromiseInterface));

        $this->assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true, $object)->fetch($loader, $repository, $promise)
        );
    }

    public function testFetchFailing(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail');

        $repository->expects($this->once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                $promise->fail(new \RuntimeException());

                return $repository;
            });

        $this->assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->fetch($loader, $repository, $promise)
        );
    }
}

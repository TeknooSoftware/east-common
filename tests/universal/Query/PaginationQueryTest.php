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
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Query\PaginationQuery;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PaginationQuery::class)]
class PaginationQueryTest extends TestCase
{
    use QueryCollectionTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(): QueryCollectionInterface
    {
        return new PaginationQuery(['foo' => 'bar'], ['foo' => 'ASC'], 12, 20);
    }

    public function testExecute(): void
    {
        $loader = $this->createStub(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())
            ->method('success')
            ->with(
                self::callback(
                    fn ($r): bool => $r instanceof \Countable
                        && $r instanceof \IteratorAggregate
                        && 20 === $r->count()
                        && $r->getIterator() instanceof \Iterator
                )
            );
        $promise->expects($this->never())->method('fail');

        $repository->expects($this->once())
            ->method('count')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn ($p): bool => $p instanceof PromiseInterface
                )
            )->willReturnCallback(
                function (array $criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success(20);

                    return $repository;
                }
            );

        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn ($p): bool => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(
                function (array $criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success($this->createStub(\Iterator::class));

                    return $repository;
                }
            );

        $this->assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteErrorOnCount(): void
    {
        $loader = $this->createStub(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail');

        $repository->expects($this->once())
            ->method('count')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn ($p): bool => $p instanceof PromiseInterface
                )
            )->willReturnCallback(
                function (array $criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(new \Exception());

                    return $repository;
                }
            );

        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn ($p): bool => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(
                function (array $criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success($this->createStub(\Iterator::class));

                    return $repository;
                }
            );

        $this->assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteErrorOnFin(): void
    {
        $loader = $this->createStub(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail');

        $repository->expects($this->once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn ($p): bool => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(
                function (array $criteria, PromiseInterface $promise) use ($repository): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(new \Exception());

                    return $repository;
                }
            );

        $this->assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }
}

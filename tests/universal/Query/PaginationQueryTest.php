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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Query;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Query\PaginationQuery;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Query\Enum\Direction
 * @covers \Teknoo\East\Common\Query\PaginationQuery
 */
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

    public function testExecute()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::once())
            ->method('success')
            ->with(
                self::callback(
                    fn($r) => $r instanceof \Countable
                        && $r instanceof \IteratorAggregate
                        && 20 === $r->count()
                        && $r->getIterator() instanceof \Iterator
                )
            );
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('count')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                )
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                $promise->success(20);

                return $repository;
            }
            );

        $repository->expects(self::once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                    $promise->success($this->createMock(\Iterator::class));

                    return $repository;
                }
            );

        self::assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteErrorOnCount()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::once())
            ->method('count')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                )
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                $promise->fail(new \Exception());

                return $repository;
            }
            );

        $repository->expects(self::once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                    $promise->success($this->createMock(\Iterator::class));

                    return $repository;
                }
            );

        self::assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteErrorOnFin()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::any())
            ->method('count')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                )
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                $promise->fail(new \Exception());

                return $repository;
            }
            );

        $repository->expects(self::once())
            ->method('findBy')
            ->with(
                ['foo' => 'bar', 'deletedAt' => null,],
                self::callback(
                    fn($p) => $p instanceof PromiseInterface
                ),
                ['foo' => 'ASC'],
                12,
                20
            )->willReturnCallback(function (array $criteria, PromiseInterface $promise) use ($repository) {
                    $promise->fail(new \Exception());

                    return $repository;
                }
            );

        self::assertInstanceOf(
            PaginationQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }
}

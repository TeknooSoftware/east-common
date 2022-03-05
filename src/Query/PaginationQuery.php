<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Query;

use Countable;
use IteratorAggregate;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Query\Enum\Direction;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Throwable;
use Traversable;

/**
 * Generic class implementing query to load any persisted instance with a pagination, order behavior and criteria
 * selections.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class PaginationQuery implements QueryInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, Direction> $order
     */
    public function __construct(
        private readonly array $criteria,
        private readonly array $order,
        private readonly int $limit,
        private readonly int $offset,
    ) {
        $this->uniqueConstructorCheck();
    }

    public function execute(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryInterface {
        $criteria = $this->criteria;
        $criteria['deletedAt'] = null;

        $failClosure = static function (Throwable $error) use ($promise) {
            $promise->fail($error);
        };

        $repository->findBy(
            $criteria,
            new Promise(
                static function ($result) use ($criteria, $promise, $repository, $failClosure) {
                    $repository->count(
                        $criteria,
                        new Promise(
                            static function ($count) use ($promise, $result) {
                                $iterator = new class ($count, $result) implements Countable, IteratorAggregate {
                                    /**
                                     * @param Traversable<ObjectInterface> $iterator
                                     */
                                    public function __construct(
                                        private readonly int $count,
                                        private readonly Traversable $iterator,
                                    ) {
                                    }

                                    /**
                                     * @return Traversable<ObjectInterface>
                                     */
                                    public function getIterator(): Traversable
                                    {
                                        return $this->iterator;
                                    }

                                    public function count(): int
                                    {
                                        return $this->count;
                                    }
                                };

                                $promise->success($iterator);
                            },
                            $failClosure
                        )
                    );
                },
                $failClosure
            ),
            $this->order,
            $this->limit,
            $this->offset
        );

        return $this;
    }
}

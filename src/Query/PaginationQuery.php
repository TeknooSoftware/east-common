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
     * @var array<string, mixed>
     */
    private array $criteria;

    /**
     * @var array<string, string>
     */
    private array $order;

    private int $limit;

    private int $offset;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string> $order
     */
    public function __construct(array $criteria, array $order, int $limit, int $offset)
    {
        $this->uniqueConstructorCheck();

        $this->criteria = $criteria;
        $this->order = $order;
        $this->limit = $limit;
        $this->offset = $offset;
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
                                     * @param iterable<ObjectInterface> $iterator
                                     */
                                    public function __construct(
                                        private int $count,
                                        private iterable $iterator,
                                    ) {
                                    }

                                    /**
                                     * @return iterable<ObjectInterface>
                                     */
                                    public function getIterator(): iterable
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

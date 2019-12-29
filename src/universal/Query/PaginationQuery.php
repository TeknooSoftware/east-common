<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Query;

use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
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

    /**
     * @inheritDoc
     */
    public function execute(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryInterface {
        $criteria = $this->criteria;
        $criteria['deletedAt'] = null;
        $repository->findBy($criteria, $promise, $this->order, $this->limit, $this->offset);

        return $this;
    }
}

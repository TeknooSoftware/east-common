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

namespace Teknoo\East\Common\Contracts\DBSource;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @template TSuccessArgType
 */
interface RepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param PromiseInterface<TSuccessArgType, mixed> $promise
     * @param string[] $hydrate list of fields hosting a reference to hydrate
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function find(
        string $id,
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface;

    /**
     * Finds all objects in the repository.
     *
     * @param PromiseInterface<iterable<TSuccessArgType>, mixed> $promise
     * @param string[] $hydrate list of fields hosting a reference to hydrate
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function findAll(
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array<string, mixed> $criteria
     * @param PromiseInterface<iterable<TSuccessArgType>, mixed> $promise
     * @param array<string, Direction>|null $orderBy
     * @param string[] $hydrate list of fields hosting a reference to hydrate
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        array $hydrate = [],
    ): RepositoryInterface;

    /**
     * Count objects with criteria compatible
     *
     * @param array<string, mixed> $criteria The criteria.
     * @param PromiseInterface<int, mixed> $promise
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function count(
        array $criteria,
        PromiseInterface $promise,
    ): RepositoryInterface;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string|int, mixed> $criteria The criteria.
     * @param PromiseInterface<TSuccessArgType, mixed> $promise
     * @param string[] $hydrate list of fields hosting a reference to hydrate
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function findOneBy(
        array $criteria,
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface;
}

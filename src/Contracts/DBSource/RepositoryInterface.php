<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\DBSource;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        array $hydrate = [],
    ): RepositoryInterface;

    /**
     * To get distinct values about a document's (sub)field according to criteria and optional offset
     *
     * @param array<string, mixed> $criteria
     * @param PromiseInterface<iterable<mixed>, mixed> $promise
     * @return RepositoryInterface<TSuccessArgType>
     */
    public function distinctBy(
        string $field,
        array $criteria,
        PromiseInterface $promise,
        ?int $limit = null,
        ?int $offset = null,
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

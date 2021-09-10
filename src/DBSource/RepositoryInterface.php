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

namespace Teknoo\East\Website\DBSource;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RepositoryInterface
{
    /*
     * Finds an object by its primary key / identifier.
     */
    public function find(string $id, PromiseInterface $promise): RepositoryInterface;

    /*
     * Finds all objects in the repository.
     */
    public function findAll(PromiseInterface $promise): RepositoryInterface;

    /*
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     */
     /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed>|null $orderBy
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): RepositoryInterface;

    /**
     * Count objects with criteria compatible
     *
     * @param array<string, mixed> $criteria The criteria.
     */
    public function count(array $criteria, PromiseInterface $promise): RepositoryInterface;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array<string|int, mixed> $criteria The criteria.
     */
    public function findOneBy(array $criteria, PromiseInterface $promise): RepositoryInterface;
}

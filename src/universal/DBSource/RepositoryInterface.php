<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\DBSource;

use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id The identifier.
     * @param PromiseInterface $promise
     *
     * @return RepositoryInterface self
     */
    public function find(string $id, PromiseInterface $promise): RepositoryInterface;

    /**
     * Finds all objects in the repository.
     *
     * @param PromiseInterface $promise
     *
     * @return RepositoryInterface self
     */
    public function findAll(PromiseInterface $promise): RepositoryInterface;

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param PromiseInterface $promise
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return RepositoryInterface self
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ): RepositoryInterface;

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     * @param PromiseInterface $promise
     *
     * @return RepositoryInterface self
     */
    public function findOneBy(array $criteria, PromiseInterface $promise): RepositoryInterface;
}
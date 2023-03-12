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

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Contract to define query executor, to use in `DeletingQueryInterface` and `UpdatingQueryInterface` as adapter between
 * your business queries and final data layer
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @template TPersistedObject
 */
interface QueryExecutorInterface
{
    /**
     * To set the domain (the class, document type, table) where perform operation and criteria to select object to
     * manipulate.
     * @param class-string<TPersistedObject> $className
     * @param array<string, mixed> $criteria
     * @return QueryExecutorInterface<TPersistedObject>
     */
    public function filterOn(string $className, array $criteria): QueryExecutorInterface;

    /**
     * To set fields to update with theirs news values
     *
     * @param array<string, mixed> $fields
     * @return QueryExecutorInterface<TPersistedObject>
     */
    public function updateFields(array $fields): QueryExecutorInterface;

    /**
     * To execute the configured query and pass the result to the promise
     *
     * @param PromiseInterface<TPersistedObject, mixed> $promise
     * @return QueryExecutorInterface<TPersistedObject>
     */
    public function execute(PromiseInterface $promise): QueryExecutorInterface;
}

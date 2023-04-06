<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\Query;

use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define query to delete one or several objects / documents
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TPersistedObject
 */
interface DeletingQueryInterface
{
    /**
     * @param QueryExecutorInterface<TPersistedObject> $queryBuilder
     * @param PromiseInterface<TPersistedObject, mixed> $promise
     * @return DeletingQueryInterface<TPersistedObject>
     */
    public function delete(
        QueryExecutorInterface $queryBuilder,
        PromiseInterface $promise,
    ): DeletingQueryInterface;
}

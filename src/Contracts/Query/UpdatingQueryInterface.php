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

namespace Teknoo\East\Common\Contracts\Query;

use Teknoo\East\Common\Contracts\DBSource\QueryExecutorInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define query to update one or several objects / documents
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
interface UpdatingQueryInterface
{
    /**
     * @param QueryExecutorInterface<TPersistedObject> $queryBuilder
     * @param PromiseInterface<TPersistedObject, mixed> $promise
     * @return UpdatingQueryInterface<TPersistedObject>
     */
    public function update(
        QueryExecutorInterface $queryBuilder,
        PromiseInterface $promise,
    ): UpdatingQueryInterface;
}

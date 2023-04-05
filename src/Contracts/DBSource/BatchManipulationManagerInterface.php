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

namespace Teknoo\East\Common\Contracts\DBSource;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\DeletingQueryInterface;
use Teknoo\East\Common\Contracts\Query\UpdatingQueryInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Extension of manager about batchs operations on persisted objects
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface BatchManipulationManagerInterface extends ManagerInterface
{
    /**
     * To update several object in a same time
     *
     * @param UpdatingQueryInterface<ObjectInterface> $query
     * @param PromiseInterface<ObjectInterface, mixed> $promise
     */
    public function updateQuery(
        UpdatingQueryInterface $query,
        PromiseInterface $promise,
    ): BatchManipulationManagerInterface;

    /**
     * To delete several objet in a same time
     *
     * @param DeletingQueryInterface<ObjectInterface> $query
     * @param PromiseInterface<ObjectInterface, mixed> $promise
     */
    public function deleteQuery(
        DeletingQueryInterface $query,
        PromiseInterface $promise,
    ): BatchManipulationManagerInterface;
}

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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\DeletingQueryInterface;
use Teknoo\East\Common\Contracts\Query\UpdatingQueryInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Manager to perform batchs operations on persisted objects. Its fallback to the default object Manager about single
 * operation (persist, remove and flush).
 * For batch operations, this manager create a QueryExecutor, available in this namespace, as adapter and pass it to the
 * business query/
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BatchManipulationManager implements BatchManipulationManagerInterface
{
    public function __construct(
        private readonly ManagerInterface $baseManager,
        private readonly DocumentManager $documentManager,
    ) {
    }

    public function updateQuery(
        UpdatingQueryInterface $query,
        PromiseInterface $promise,
    ): BatchManipulationManagerInterface {
        $executor = new QueryExecutor(
            $this->documentManager,
            static fn(Builder $builder): Builder => $builder->updateMany(),
        );

        $query->update($executor, $promise);

        return $this;
    }

    public function deleteQuery(
        DeletingQueryInterface $query,
        PromiseInterface $promise,
    ): BatchManipulationManagerInterface {
        $executor = new QueryExecutor(
            $this->documentManager,
            static fn(Builder $builder): Builder => $builder->remove(),
        );

        $query->delete($executor, $promise);

        return $this;
    }

    public function persist(ObjectInterface $object): ManagerInterface
    {
        $this->baseManager->persist($object);

        return $this;
    }

    public function remove(ObjectInterface $object): ManagerInterface
    {
        $this->baseManager->remove($object);

        return $this;
    }

    public function flush(): ManagerInterface
    {
        $this->baseManager->flush();

        return $this;
    }
}

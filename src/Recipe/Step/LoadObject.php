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

namespace Teknoo\East\Common\Recipe\Step;

use DomainException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Generic step recipe to load, from its id, a persisted object thank to its loader and put it into the workplan
 * at key defined in the ingredient $workPlanKey (by default `IdentifiedObjectInterface::class`)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LoadObject
{
    /**
     * @param \Teknoo\East\Common\Contracts\Loader\LoaderInterface<ObjectInterface> $loader
     */
    public function __invoke(
        LoaderInterface $loader,
        string $id,
        ManagerInterface $manager,
        string $workPlanKey = ObjectInterface::class,
    ): self {
        /** @var Promise<ObjectInterface, mixed, mixed> $fetchPromise */
        $fetchPromise = new Promise(
            static function (ObjectInterface $object) use ($manager, $workPlanKey) {
                $manager->updateWorkPlan([$workPlanKey => $object]);
            },
            static function (Throwable $error) use ($manager) {
                $error = new DomainException($error->getMessage(), 404, $error);
                $manager->error($error);
            }
        );

        $loader->load(
            $id,
            $fetchPromise
        );

        return $this;
    }
}

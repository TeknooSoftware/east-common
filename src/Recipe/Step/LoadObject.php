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

namespace Teknoo\East\Website\Recipe\Step;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Throwable;

/**
 * Generic step recipe to load, from its id, a persisted object thank to its loader and put it into the workplan
 * at key defined in the ingredient $workPlanKey (by default `ObjectInterface::class`)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LoadObject
{
    public function __invoke(
        LoaderInterface $loader,
        string $id,
        ManagerInterface $manager,
        string $workPlanKey = ObjectInterface::class,
    ): self {
        $loader->load(
            $id,
            new Promise(
                static function (ObjectInterface $object) use ($manager, $workPlanKey) {
                    $manager->updateWorkPlan([$workPlanKey => $object]);
                },
                static function (Throwable $error) use ($manager) {
                    $error = new DomainException($error->getMessage(), 404, $error);
                    $manager->error($error);
                }
            )
        );

        return $this;
    }
}

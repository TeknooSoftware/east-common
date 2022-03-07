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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use DomainException;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;

use function class_exists;
use function is_array;

/**
 * Recipe step to create a new `Teknoo\East\Website\Contracts\ObjectInterface` to be use/populate in next step of
 * recipes (in a form, a view, etc...). The object will be put in the manager's workplan at the key `$workPlanKey`
 * (by default  ObjectInterface::class).
 *
 * Constructor Arguments can be passed as`$constructorArguments` (If several arguments must be passed, they must be
 * passed as array).
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CreateObject
{
    /**
     * @param array<int|string, mixed>|mixed $constructorArguments
     */
    public function __invoke(
        string $objectClass,
        ManagerInterface $manager,
        mixed $constructorArguments = null,
        string $workPlanKey = ObjectInterface::class,
    ): self {
        if (!class_exists($objectClass)) {
            $error = new DomainException("Error class $objectClass is not available");

            $manager->error($error);

            return $this;
        }

        if (null !== $constructorArguments) {
            if (!is_array($constructorArguments)) {
                $constructorArguments = [$constructorArguments];
            }

            $object = new $objectClass(...$constructorArguments);
        } else {
            $object = new $objectClass();
        }

        if (!$object instanceof ObjectInterface) {
            $error = new RuntimeException("Error $objectClass is not a ObjectInterface");

            $manager->error($error);

            return $this;
        }

        $manager->updateWorkPlan([$workPlanKey => $object]);

        return $this;
    }
}

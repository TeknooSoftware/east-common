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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use DomainException;
use RuntimeException;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function class_exists;
use function is_array;

/**
 * Recipe step to create a new `Teknoo\East\Common\Contracts\IdentifiedObjectInterface` to be use/populate in next step
 * of recipes (in a form, a view, etc...). The object will be put in the manager's workplan at the key `$workPlanKey`
 * (by default  IdentifiedObjectInterface::class).
 *
 * Constructor Arguments can be passed as`$constructorArguments` (If several arguments must be passed, they must be
 * passed as array).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
        int $errorCode = 400,
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
            $error = new RuntimeException(
                "Error $objectClass is not a IdentifiedObjectInterface",
                400,
            );

            $manager->error($error);

            return $this;
        }

        $manager->updateWorkPlan([$workPlanKey => $object]);

        return $this;
    }
}

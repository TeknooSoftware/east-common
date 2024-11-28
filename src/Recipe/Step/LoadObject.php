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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use DomainException;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Generic step recipe to load, from its id, a persisted object thank to its loader and put it into the workplan
 * at key defined in the ingredient $workPlanKey (by default `IdentifiedObjectInterface::class`)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadObject
{
    /**
     * @param LoaderInterface<ObjectInterface> $loader
     */
    public function __invoke(
        LoaderInterface $loader,
        string $id,
        ManagerInterface $manager,
        string $workPlanKey = ObjectInterface::class,
        ?string $errorMessage = null,
        ?int $errorCode = null,
    ): self {
        /** @var Promise<ObjectInterface, mixed, mixed> $fetchPromise */
        $fetchPromise = new Promise(
            static function (ObjectInterface $object) use ($manager, $workPlanKey): void {
                $manager->updateWorkPlan([$workPlanKey => $object]);
            },
            static fn (#[SensitiveParameter] Throwable $error): ChefInterface => $manager->error(
                new DomainException(
                    message: $errorMessage ?? $error->getMessage(),
                    code: $errorCode ?? (int) ($error->getCode() > 0 ? $error->getCode() : 404),
                    previous: $error,
                )
            ),
        );

        $loader->load(
            $id,
            $fetchPromise,
        );

        return $this;
    }
}

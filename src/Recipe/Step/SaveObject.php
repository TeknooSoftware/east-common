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

use RuntimeException;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface as ObjectWithId;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Recipe step to save/persist a persistable object into a database thanks to its dedicated writer passed also as
 * ingredient
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SaveObject
{
    /**
     * @param WriterInterface<ObjectInterface> $writer
     * @param array<string, mixed> $parameters
     *
     */
    public function __invoke(
        WriterInterface $writer,
        ObjectInterface $object,
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        string $errorMessage = 'Error during object persistence',
        int $errorCode = 500,
        ?bool $preferRealDateOnUpdate = null,
        array $parameters = [],
    ): self {
        /** @var Promise<ObjectInterface, mixed, mixed> $savedPromise */
        $savedPromise = new Promise(
            static function (ObjectInterface $object) use ($manager, $parametersBag, &$parameters): void {
                $workplan = [
                    'formHandleRequest' => false,
                    'objectSaved' => true,
                ];

                $parametersBag->set('objectSaved', true);

                if ($object instanceof ObjectWithId) {
                    $workplan['id'] = $object->getId();
                    $parameters['id'] = $object->getId();
                    $parameters['objectSaved'] = true;
                    $workplan['parameters'] = $parameters;
                }

                $manager->updateWorkPlan($workplan);
            },
            static fn (#[SensitiveParameter] Throwable $error): ChefInterface => $manager->error(
                new RuntimeException(
                    code: $errorCode,
                    message: $errorMessage,
                    previous: $error,
                )
            )
        );

        $writer->save(
            object: $object,
            promise: $savedPromise,
            preferRealDateOnUpdate: $preferRealDateOnUpdate,
        );

        return $this;
    }
}

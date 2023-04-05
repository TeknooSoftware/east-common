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

namespace Teknoo\East\Common\Recipe\Step;

use RuntimeException;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SaveObject
{
    /**
     * @param WriterInterface<\Teknoo\East\Common\Contracts\Object\ObjectInterface> $writer
     */
    public function __invoke(
        WriterInterface $writer,
        ObjectInterface $object,
        ManagerInterface $manager,
        ParametersBag $parametersBag,
        string $errorMessage = 'Error during object persistence',
        int $errorCode = 500,
        ?bool $prefereRealDateOnUpdate = null,
    ): self {
        /** @var Promise<\Teknoo\East\Common\Contracts\Object\ObjectInterface, mixed, mixed> $savedPromise */
        $savedPromise = new Promise(
            static function (ObjectInterface $object) use ($manager, $parametersBag): void {
                $workplan = [
                    'formHandleRequest' => false,
                    'objectSaved' => true,
                ];

                $parametersBag->set('objectSaved', true);

                if ($object instanceof ObjectWithId) {
                    $workplan['id'] = $object->getId();
                    $workplan['parameters'] = [
                        'id' => $object->getId(),
                    ];
                }

                $manager->updateWorkPlan($workplan);
            },
            static fn (Throwable $error): ChefInterface => $manager->error(
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
            prefereRealDateOnUpdate: $prefereRealDateOnUpdate,
        );

        return $this;
    }
}

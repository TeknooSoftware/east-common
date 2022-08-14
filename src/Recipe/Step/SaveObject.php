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

use RuntimeException;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface as ObjectWithId;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Recipe step to save/persist a persistable object into a database thanks to its dedicated writer passed also as
 * ingredient
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
        string $errorMessage = 'Error during object persistence',
        int $errorCode = 500,
        ?bool $prefereRealDateOnUpdate = null,
    ): self {
        /** @var Promise<\Teknoo\East\Common\Contracts\Object\ObjectInterface, mixed, mixed> $savedPromise */
        $savedPromise = new Promise(
            static function (ObjectInterface $object) use ($manager) {
                if ($object instanceof ObjectWithId) {
                    $manager->updateWorkPlan([
                        'id' => $object->getId(),
                        'parameters' => [
                            'id' => $object->getId(),
                        ],
                    ]);
                }
            },
            static fn (Throwable $error) => $manager->error(
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

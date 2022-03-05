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

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Object\ObjectInterface as ObjectWithId;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * Recipe step to save/persist a persistable object into a database thanks to its dedicated writer passed also as
 * ingredient
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SaveObject
{
    public function __invoke(
        WriterInterface $writer,
        ObjectInterface $object,
        ManagerInterface $manager,
    ): self {
        $writer->save(
            $object,
            new Promise(
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
                $manager->error(...)
            )
        );

        return $this;
    }
}

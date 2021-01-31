<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
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
use Teknoo\East\Website\Object\ObjectInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CreateObject
{
    public function __invoke(
        string $objectClass,
        ManagerInterface $manager
    ): self {
        if (!\class_exists($objectClass)) {
            $error = new \DomainException("Error class $objectClass is not available");

            $manager->error($error);

            return $this;
        }

        $object = new $objectClass();
        if (!$object instanceof ObjectInterface) {
            $error = new \RuntimeException("Error $objectClass is not a ObjectInterface");

            $manager->error($error);

            return $this;
        }

        $manager->updateWorkPlan([ObjectInterface::class => $object]);

        return $this;
    }
}
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\FrontAsset\Extensions;

use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Foundation\Extension\Manager;
use Teknoo\East\Foundation\Extension\ManagerInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @phpstan-consistent-constructor
 */
class SourceLoader implements ModuleInterface
{
    public function __construct(
        private readonly ManagerInterface $manager,
        private readonly ?FileType $type = null,
        private readonly ?string $setName = null,
        private readonly ?FilesSet $sets = null,
    ) {
    }

    public function update(callable $modifier): self
    {
        if ($this->type && $this->setName && $this->sets) {
            $modifier($this->type, $this->setName, $this->sets);
        }

        return $this;
    }

    public function updateSets(
        FileType $type,
        string $setName,
        FilesSet $sets
    ): void {
        $module = new static($this->manager, $type, $setName, $sets);

        $this->manager->execute($module);
    }
}

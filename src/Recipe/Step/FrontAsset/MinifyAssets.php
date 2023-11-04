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

namespace Teknoo\East\Common\Recipe\Step\FrontAsset;

use Teknoo\East\Common\Contracts\FrontAsset\FilesSetInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MinifyAssets
{
    public function __invoke(
        ManagerInterface $manager,
        MinifierInterface $minifier,
        FilesSetInterface $set,
        string $assetPath,
    ): self {
        $minifier->process(
            set: $set,
            fileName: $assetPath,
            holder: fn (FinalFile $file) => $manager->updateWorkPlan([
               FinalFile::class => $file,
            ]),
        );

        return $this;
    }
}

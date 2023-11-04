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

namespace Teknoo\East\Common\Minify;

use MatthiasMullie\Minify\Minify;
use Teknoo\East\Common\Contracts\FrontAsset\FilesSetInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractMinifier implements MinifierInterface
{
    public function __construct(
        private readonly Minify $minifier,
    ) {
    }

    protected function run(
        FilesSetInterface $set,
        callable $holder,
        FileType $type,
        string $path,
    ): void {
        $minifier = clone $this->minifier;

        foreach ($set as $file) {
            $minifier->add($file->getContent());
        }

        $holder(
            new FinalFile(
                path: $path,
                type: $type,
                contentCallback: fn () => $minifier->execute(),
            )
        );
    }
}

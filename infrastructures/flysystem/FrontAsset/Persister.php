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

namespace Teknoo\East\Common\Flysystem\FrontAsset;

use League\Flysystem\Filesystem;
use Teknoo\East\Common\Contracts\FrontAsset\FileInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Persister implements PersisterInterface
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    public function load(string $path, FileType $type, callable $holder): PersisterInterface
    {
        if ($this->filesystem->fileExists($path)) {
            $holder(
                new FinalFile(
                    path: $path,
                    type: $type,
                    contentCallback: fn () => $this->filesystem->read($path),
                ),
            );
        }

        return $this;
    }

    public function write(FileInterface $file): PersisterInterface
    {
        $this->filesystem->write(
            $file->getPath(),
            $file->getContent(),
        );

        return $this;
    }
}

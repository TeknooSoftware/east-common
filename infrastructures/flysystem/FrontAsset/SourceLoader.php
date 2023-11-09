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
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\FrontAsset\Exception\UnkownSetNameException;
use Teknoo\East\Common\FrontAsset\File;
use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SourceLoader implements SourceLoaderInterface
{
    /**
     * @param array<string, string[]> $definedSets
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly array $definedSets,
        private readonly FileType $type,
    ) {
    }

    public function load(string $setName, callable $holder): SourceLoaderInterface
    {
        if (!isset($this->definedSets[$setName])) {
            throw new UnkownSetNameException("The set `$setName` is not defined for `{$this->type->value}`");
        }

        $set = new FilesSet();
        foreach ($this->definedSets[$setName] as $file) {
            $set->add(
                new File(
                    path: $file,
                    type: $this->type,
                    contentCallback: fn () => $this->filesystem->read($file),
                )
            );
        }

        $holder($set);

        return $this;
    }
}

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

namespace Teknoo\Tests\East\Common\Behat\Extension;

use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader;
use Teknoo\East\Common\FrontAsset\File;
use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Foundation\Extension\ExtensionInitTrait;
use Teknoo\East\Foundation\Extension\ExtensionInterface;
use Teknoo\East\Foundation\Extension\ModuleInterface;

class ExtensionForTest implements ExtensionInterface
{
    use ExtensionInitTrait;

    public function executeFor(ModuleInterface $module): ExtensionInterface
    {
        match ($module::class) {
            SourceLoader::class => $module->update(
                fn (FileType $fileType, string $setName, FilesSet $set) => match ($fileType) {
                    FileType::CSS => match ($setName) {
                            'main' => $set->add(
                                new File(
                                    'from-extension.css',
                                    FileType::CSS,
                                    fn () => 'body { background-color: red; }',
                                ),
                            ),
                            default => $set->add(
                                new File(
                                    'from-extension.css',
                                    FileType::CSS,
                                    fn () => 'body { background-color: blue; }',
                                ),
                            ),
                        },

                        FileType::JS => match ($setName) {
                            'main' => $set->add(
                                    new File(
                                        'from-extension-1.js',
                                        FileType::JS,
                                        fn () => 'alert("hello");',
                                    )
                                )->add(
                                    new File(
                                        'from-extension-2.js',
                                        FileType::JS,
                                        fn () => 'alert("world");',
                                    ),
                                ),
                            default => $set->add(
                                new File(
                                    'from-extension-fail.js',
                                    FileType::JS,
                                    fn () => 'alert("fail");',
                                )
                            )
                    },
                    default => null,
                }
            ),
            default => null,
        };

        return $this;
    }

    public function __toString(): string
    {
        return 'Test';
    }
}
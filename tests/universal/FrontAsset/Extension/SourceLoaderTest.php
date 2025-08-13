<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\FrontAsset\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader;
use Teknoo\East\Common\FrontAsset\File;
use Teknoo\East\Common\FrontAsset\FilesSet;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Foundation\Extension\ManagerInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SourceLoader::class)]
class SourceLoaderTest extends TestCase
{
    public function testExtendsBundles(): void
    {
        $toCall = fn (): string => 'foo';
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($module) use ($manager, $toCall): \PHPUnit\Framework\MockObject\MockObject {
                    $this->assertinstanceOf(SourceLoader::class, $module);

                    $module->update(
                        fn (FileType $fileType, string $setName, FilesSet $set): \Teknoo\East\Common\Contracts\FrontAsset\FilesSetInterface => match ($fileType) {
                            FileType::JS => $set->add(
                                new File(
                                    'bar2.js',
                                    FileType::JS,
                                    $toCall,
                                )
                            ),
                            FileType::CSS => $set->add(
                                new File(
                                    'bar2.css',
                                    FileType::CSS,
                                    $toCall
                                )
                            ),
                        }
                    );

                    return $manager;
                }
            );

        $set = $this->createMock(FilesSet::class);
        $set->expects($this->once())
            ->method('add')
            ->with(
                new File(
                    'bar2.css',
                    FileType::CSS,
                    $toCall
                )
            );

        new SourceLoader($manager)->updateSets(
            FileType::CSS,
            'default',
            $set,
        );
    }
}

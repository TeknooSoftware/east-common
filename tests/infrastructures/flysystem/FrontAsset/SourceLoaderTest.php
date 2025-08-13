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

namespace Teknoo\Tests\East\Common\Flysystem\FrontAsset;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Flysystem\FrontAsset\SourceLoader;
use Teknoo\East\Common\FrontAsset\Exception\UnkownSetNameException;
use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader as SourceLoaderExtension;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(SourceLoader::class)]
class SourceLoaderTest extends TestCase
{
    public function testLoadMissingSet(): void
    {
        $flysystem = $this->createMock(Filesystem::class);

        $this->expectException(UnkownSetNameException::class);
        $loader = new SourceLoader(
            $this->createMock(SourceLoaderExtension::class),
            $flysystem,
            [
                'foo' => [],
            ],
            FileType::CSS,
        );

        $loader->load(
            'bar',
            fn ($x) => $x,
        );
    }

    public function testLoad(): void
    {
        $flysystem = $this->createMock(Filesystem::class);

        $loader = new SourceLoader(
            $this->createMock(SourceLoaderExtension::class),
            $flysystem,
            [
                'foo' => [
                    'bar'
                ],
            ],
            FileType::CSS,
        );

        $this->assertInstanceOf(
            SourceLoader::class,
            $loader->load(
                'foo',
                fn ($x) => $x,
            ),
        );
    }
}

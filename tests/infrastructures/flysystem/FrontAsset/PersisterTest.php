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

namespace Teknoo\Tests\East\Common\Flysystem\FrontAsset;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\FrontAsset\FileInterface;
use Teknoo\East\Common\Flysystem\FrontAsset\Persister;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Persister::class)]
class PersisterTest extends TestCase
{
    public function testLoad(): void
    {
        $flysystem = $this->createMock(Filesystem::class);
        $flysystem->expects($this->any())
            ->method('fileExists')
            ->willReturn(true);

        $persister = new Persister($flysystem);
        self::assertInstanceOf(
            Persister::class,
            $persister->load(
                'foo',
                FileType::CSS,
                fn ($x) => $x,
            ),
        );
    }

    public function testWrite(): void
    {
        $flysystem = $this->createMock(Filesystem::class);
        $flysystem->expects($this->any())
            ->method('write');

        $persister = new Persister($flysystem);
        self::assertInstanceOf(
            Persister::class,
            $persister->write(
                $this->createMock(FileInterface::class),
            ),
        );
    }
}

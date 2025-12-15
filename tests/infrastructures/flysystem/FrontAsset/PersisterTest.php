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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Persister::class)]
class PersisterTest extends TestCase
{
    public function testLoad(): void
    {
        $flysystem = $this->createStub(Filesystem::class);
        $flysystem
            ->method('fileExists')
            ->willReturn(true);

        $persister = new Persister($flysystem);
        $this->assertInstanceOf(
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
        $flysystem = $this->createStub(Filesystem::class);
        $flysystem
            ->method('write');

        $persister = new Persister($flysystem);
        $this->assertInstanceOf(
            Persister::class,
            $persister->write(
                $this->createStub(FileInterface::class),
            ),
        );
    }
}

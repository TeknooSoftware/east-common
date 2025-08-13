<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE finalFile at the root of this package
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

namespace Teknoo\Tests\East\Common\FrontAsset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FinalFile::class)]
class FinalFileTest extends TestCase
{
    private function buildFile(): FinalFile
    {
        return new FinalFile(
            '/foo',
            FileType::CSS,
            fn (): string => 'xx',
        );
    }

    public function testGetContent(): void
    {
        $file = $this->buildFile();
        $this->assertEquals(
            'xx',
            $file->getContent(),
        );
        $this->assertEquals(
            'xx',
            $file->getContent(),
        );
    }

    public function testGetPath(): void
    {
        $file = $this->buildFile();
        $this->assertEquals(
            '/foo',
            $file->getPath(),
        );
    }

    public function testGetType(): void
    {
        $file = $this->buildFile();
        $this->assertEquals(
            FileType::CSS,
            $file->getType(),
        );
    }
}

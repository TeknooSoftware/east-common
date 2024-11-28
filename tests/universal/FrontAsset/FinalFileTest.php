<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source finalFile is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\FrontAsset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @license     https://teknoo.software/license/mit         MIT License
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
            fn () => 'xx',
        );
    }

    public function testGetContent()
    {
        $file = $this->buildFile();
        self::assertEquals(
            'xx',
            $file->getContent(),
        );
        self::assertEquals(
            'xx',
            $file->getContent(),
        );
    }

    public function testGetPath()
    {
        $file = $this->buildFile();
        self::assertEquals(
            '/foo',
            $file->getPath(),
        );
    }

    public function testGetType()
    {
        $file = $this->buildFile();
        self::assertEquals(
            FileType::CSS,
            $file->getType(),
        );
    }
}

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

namespace Teknoo\Tests\East\Common\FrontAsset;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\FrontAsset\File;
use Teknoo\East\Common\FrontAsset\FileType;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\Common\FrontAsset\File
 */
class FileTest extends TestCase
{
    private function buildFile(): File
    {
        return new File(
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
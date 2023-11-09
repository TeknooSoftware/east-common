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
use Teknoo\East\Common\Contracts\FrontAsset\FileInterface;
use Teknoo\East\Common\FrontAsset\FilesSet;

use function iterator_to_array;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\Common\FrontAsset\FilesSet
 */
class FilesSetTest extends TestCase
{
    private function buildSet(): FilesSet
    {
        return new FilesSet(
            [
                $this->createMock(FileInterface::class),
            ]
        );
    }

    public function testAdd()
    {
        self::assertInstanceOf(
            FilesSet::class,
            $this->buildSet()->add(
                $this->createMock(FileInterface::class),
            )
        );
    }

    public function testIterator()
    {
        $set = $this->buildSet()->add(
            $this->createMock(FileInterface::class),
        );

        self::assertCount(
            2,
            iterator_to_array($set)
        );
    }
}

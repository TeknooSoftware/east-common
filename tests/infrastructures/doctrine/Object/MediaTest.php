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

namespace Teknoo\Tests\East\Common\Doctrine\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\Media as MediaOrigin;
use Teknoo\Tests\East\Common\Object\MediaTest as OriginalTest;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(MediaOrigin::class)]
#[CoversClass(Media::class)]
class MediaTest extends OriginalTest
{
    #[\Override]
    public function buildObject(): Media
    {
        return new Media();
    }

    public function testGetChunkSize(): void
    {
        $this->assertNull($this->generateObjectPopulated()->getChunkSize());
        $this->assertIsInt($this->generateObjectPopulated(['chunkSize' => 124])->getChunkSize());
    }

    public function testGetUploadDate(): void
    {
        $this->assertNull($this->generateObjectPopulated()->getUploadDate());
        $this->assertInstanceOf(
            \DateTimeInterface::class,
            $this->generateObjectPopulated(['uploadDate' => (new \DateTime('2020-08-01'))])->getUploadDate()
        );
    }
}

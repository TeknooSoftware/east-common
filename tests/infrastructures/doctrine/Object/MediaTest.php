<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\Object;

use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\Tests\East\Common\Object\MediaTest as OriginalTest;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Common\Doctrine\Object\Media
 * @covers \Teknoo\East\Common\Object\Media
 */
class MediaTest extends OriginalTest
{
    public function buildObject(): Media
    {
        return new Media();
    }

    public function testGetChunkSize()
    {
        self::assertNull($this->generateObjectPopulated()->getChunkSize());
        self::assertIsInt($this->generateObjectPopulated(['chunkSize' => 124])->getChunkSize());
    }

    public function testGetUploadDate()
    {
        self::assertNull($this->generateObjectPopulated()->getUploadDate());
        self::assertInstanceOf(
            \DateTimeInterface::class,
            $this->generateObjectPopulated(['uploadDate' => (new \DateTime('2020-08-01'))])->getUploadDate()
        );
    }
}

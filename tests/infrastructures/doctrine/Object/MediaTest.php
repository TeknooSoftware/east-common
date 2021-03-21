<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Doctrine\Object;

use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\Tests\East\Website\Object\MediaTest as OriginalTest;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Object\Media
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Media
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

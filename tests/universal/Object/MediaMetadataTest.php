<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\MediaMetadata;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\MediaMetadata
 */
class MediaMetadataTest extends TestCase
{
    public function buildObject(): MediaMetadata
    {
        return new MediaMetadata('contentType', 'fileName', 'alternative');
    }

    public function testGetContentType()
    {
        self::assertEquals('contentType', $this->buildObject()->getContentType());
    }

    public function testGetFileName()
    {
        self::assertEquals('fileName', $this->buildObject()->getFileName());
    }

    public function testGetAlternative()
    {
        self::assertEquals('alternative', $this->buildObject()->getAlternative());
    }
}
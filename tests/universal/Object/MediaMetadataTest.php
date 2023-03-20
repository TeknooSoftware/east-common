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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\MediaMetadata;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Object\MediaMetadata
 */
class MediaMetadataTest extends TestCase
{
    public function buildObject(): MediaMetadata
    {
        return new MediaMetadata('contentType', 'fileName', 'alternative', 'path', 'legacy');
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

    public function testGetLocalPath()
    {
        self::assertEquals('path', $this->buildObject()->getLocalPath());
    }

    public function testGetLegacyId()
    {
        self::assertEquals('legacy', $this->buildObject()->getLegacyId());
    }
}

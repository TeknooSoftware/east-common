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

namespace Teknoo\Tests\East\Website\Writer;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Writer\MediaWriter
 * @covers \Teknoo\East\Website\Writer\PersistTrait
 */
class MediaWriterTest extends TestCase
{
    use PersistTestTrait;

    public function buildWriter(): WriterInterface
    {
        return new MediaWriter($this->getObjectManager(), $this->getDatesServiceMock());
    }

    public function getObject()
    {
        return new class extends Media {
            public function getResource()
            {
                return null;
            }
        };
    }
}

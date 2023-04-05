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

namespace Teknoo\Tests\East\Common\Writer;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Writer\MediaWriter;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Writer\MediaWriter
 */
class MediaWriterTest extends TestCase
{
    use PersistTestTrait;

    public function buildWriter(bool $prefereRealDateOnUpdate = false,): WriterInterface
    {
        return new MediaWriter($this->getObjectManager(), $this->getDatesServiceMock(), $prefereRealDateOnUpdate);
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

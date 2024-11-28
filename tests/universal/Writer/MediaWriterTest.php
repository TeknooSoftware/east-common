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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Writer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Writer\MediaWriter;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MediaWriter::class)]
class MediaWriterTest extends TestCase
{
    use PersistTestTrait;

    public function buildWriter(bool $preferRealDateOnUpdate = false,): WriterInterface
    {
        return new MediaWriter($this->getObjectManager(), $this->getDatesServiceMock(), $preferRealDateOnUpdate);
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

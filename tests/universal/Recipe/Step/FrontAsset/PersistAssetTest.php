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

namespace Teknoo\Tests\East\Common\Recipe\Step\FrontAsset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PersistAsset::class)]
class PersistAssetTest extends TestCase
{
    public function testInvoke()
    {
        self::assertInstanceOf(
            PersistAsset::class,
            (new PersistAsset())(
                $this->createMock(FinalFile::class),
                $this->createMock(PersisterInterface::class),
            ),
        );
    }
}

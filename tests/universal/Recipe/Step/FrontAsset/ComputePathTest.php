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

namespace Teknoo\Tests\East\Common\Recipe\Step\FrontAsset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ComputePath;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ComputePath::class)]
class ComputePathTest extends TestCase
{
    public function testInvoke()
    {
        self::assertInstanceOf(
            ComputePath::class,
            (new ComputePath('build/'))(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ServerRequestInterface::class),
            ),
        );
    }
}

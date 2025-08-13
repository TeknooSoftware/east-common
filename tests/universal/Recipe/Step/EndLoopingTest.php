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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EndLooping::class)]
class EndLoopingTest extends TestCase
{
    public function buildStep(): EndLooping
    {
        return new EndLooping();
    }

    public function testInvokeLoopNotEnded(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue')
            ->with([], StartLoopingOn::class)
            ->willReturnSelf();

        $loop = $this->createMock(StartLoopingOn::class);
        $loop
            ->method('isEnded')
            ->willReturn(false);

        $this->assertInstanceOf(
            EndLooping::class,
            $this->buildStep()($manager, $loop)
        );
    }

    public function testInvokeLoopEnded(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $loop = $this->createMock(StartLoopingOn::class);
        $loop
            ->method('isEnded')
            ->willReturn(true);

        $this->assertInstanceOf(
            EndLooping::class,
            $this->buildStep()($manager, $loop)
        );
    }
}

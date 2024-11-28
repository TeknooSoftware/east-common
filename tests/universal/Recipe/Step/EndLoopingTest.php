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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EndLooping::class)]
class EndLoopingTest extends TestCase
{
    public function buildStep(): EndLooping
    {
        return new EndLooping();
    }

    public function testInvokeLoopNotEnded()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue')
            ->with([], StartLoopingOn::class)
            ->willReturnSelf();

        $loop = $this->createMock(StartLoopingOn::class);
        $loop->expects($this->any())
            ->method('isEnded')
            ->willReturn(false);

        self::assertInstanceOf(
            EndLooping::class,
            $this->buildStep()($manager, $loop)
        );
    }

    public function testInvokeLoopEnded()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $loop = $this->createMock(StartLoopingOn::class);
        $loop->expects($this->any())
            ->method('isEnded')
            ->willReturn(true);

        self::assertInstanceOf(
            EndLooping::class,
            $this->buildStep()($manager, $loop)
        );
    }
}

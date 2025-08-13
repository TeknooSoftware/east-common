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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use function array_shift;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(StartLoopingOn::class)]
class StartLoopingOnTest extends TestCase
{
    public function buildStep(?string $keyValue = null): StartLoopingOn
    {
        return new StartLoopingOn(keyValue: $keyValue);
    }

    public function testLoopingWithScalarAndWithoutKey(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('updateWorkPlan');

        $manager->expects($this->never())
            ->method('continue');

        $manager->expects($this->once())
            ->method('error');

        $this->assertInstanceOf(
            StartLoopingOn::class,
            $this->buildStep()($manager, [1,2,3]),
        );
    }

    public function testLoopingWithObjects(): void
    {
        $step = $this->buildStep();

        $expectedValues = [
            new DateTimeImmutable('2024-02-21'),
            new DateTimeImmutable('2024-02-22'),
            new DateTimeImmutable('2024-02-23'),
        ];

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->exactly(3))
            ->method('updateWorkPlan')
            ->willReturnCallback(
                function ($values) use (&$expectedValues, $step, $manager): ManagerInterface {
                    $this->assertEquals(
                        $values,
                        [
                            StartLoopingOn::class => $step,
                            DateTimeImmutable::class => array_shift($expectedValues),
                        ],
                    );

                    return $manager;
                }
            );

        $manager->expects($this->once())
            ->method('continue')
            ->with([StartLoopingOn::class => $step], EndLooping::class);

        $manager->expects($this->never())
            ->method('error');

        $collection = [
            new DateTimeImmutable('2024-02-21'),
            new DateTimeImmutable('2024-02-22'),
            new DateTimeImmutable('2024-02-23'),
        ];

        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertFalse($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertFalse($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertTrue($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertTrue($step->isEnded());
    }

    public function testLoopingWithScalar(): void
    {
        $step = $this->buildStep('foo');

        $expectedValues = [1,2,3];
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->exactly(3))
            ->method('updateWorkPlan')
            ->willReturnCallback(
                function ($values) use (&$expectedValues, $step, $manager): ManagerInterface {
                    $this->assertEquals(
                        $values,
                        [
                            StartLoopingOn::class => $step,
                            'foo' => array_shift($expectedValues),
                        ],
                    );

                    return $manager;
                }
            );

        $manager->expects($this->once())
            ->method('continue')
            ->with([StartLoopingOn::class => $step], EndLooping::class);

        $manager->expects($this->never())
            ->method('error');

        $collection = [1,2,3];

        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertFalse($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertFalse($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertTrue($step->isEnded());
        $this->assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        $this->assertTrue($step->isEnded());

    }
}

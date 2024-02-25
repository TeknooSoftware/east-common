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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Step\EndLooping;
use Teknoo\East\Common\Recipe\Step\StartLoopingOn;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use function array_shift;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Recipe\Step\StartLoopingOn
 */
class StartLoopingOnTest extends TestCase
{
    public function buildStep(string $keyValue = null): StartLoopingOn
    {
        return new StartLoopingOn(keyValue: $keyValue);
    }

    public function testLoopingWithScalarAndWithoutKey()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())
            ->method('updateWorkPlan');

        $manager->expects(self::never())
            ->method('continue');

        $manager->expects(self::once())
            ->method('error');

        self::assertInstanceOf(
            StartLoopingOn::class,
            $this->buildStep()($manager, [1,2,3]),
        );
    }

    public function testLoopingWithObjects()
    {
        $step = $this->buildStep();

        $expectedValues = [
            new DateTimeImmutable('2024-02-21'),
            new DateTimeImmutable('2024-02-22'),
            new DateTimeImmutable('2024-02-23'),
        ];

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::exactly(3))
            ->method('updateWorkPlan')
            ->willReturnCallback(
                function ($values) use (&$expectedValues, $step, $manager): ManagerInterface {
                    self::assertEquals(
                        $values,
                        [
                            StartLoopingOn::class => $step,
                            DateTimeImmutable::class => array_shift($expectedValues),
                        ],
                    );

                    return $manager;
                }
            );

        $manager->expects(self::once())
            ->method('continue')
            ->with([StartLoopingOn::class => $step], EndLooping::class);

        $manager->expects(self::never())
            ->method('error');

        $collection = [
            new DateTimeImmutable('2024-02-21'),
            new DateTimeImmutable('2024-02-22'),
            new DateTimeImmutable('2024-02-23'),
        ];

        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertFalse($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertFalse($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertTrue($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertTrue($step->isEnded());
    }

    public function testLoopingWithScalar()
    {
        $step = $this->buildStep('foo');

        $expectedValues = [1,2,3];
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::exactly(3))
            ->method('updateWorkPlan')
            ->willReturnCallback(
                function ($values) use (&$expectedValues, $step, $manager): ManagerInterface {
                    self::assertEquals(
                        $values,
                        [
                            StartLoopingOn::class => $step,
                            'foo' => array_shift($expectedValues),
                        ],
                    );

                    return $manager;
                }
            );

        $manager->expects(self::once())
            ->method('continue')
            ->with([StartLoopingOn::class => $step], EndLooping::class);

        $manager->expects(self::never())
            ->method('error');

        $collection = [1,2,3];

        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertFalse($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertFalse($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertTrue($step->isEnded());
        self::assertInstanceOf(StartLoopingOn::class, $step($manager, $collection));
        self::assertTrue($step->isEnded());

    }
}

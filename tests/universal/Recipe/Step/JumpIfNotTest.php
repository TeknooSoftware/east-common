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

use PHPUnit\Framework\TestCase;
use Stringable;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Recipe\Step\JumpIfNot
 */
class JumpIfNotTest extends TestCase
{
    public function buildStep(): JumpIfNot
    {
        return new JumpIfNot();
    }

    public function testInvokeNoValue()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
            )
        );
    }

    public function testInvokeEmptyStringable()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return '';
                    }
                }
            )
        );
    }

    public function testInvokeWithValueWithoutExpected()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo'
            )
        );
    }

    public function testInvokeWithValueWithoutExpectedAndStringable()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                new class implements Stringable {
                    public function __toString(): string
                    {
                        return 'foo';
                    }
                }
            )
        );
    }

    public function testInvokeValueNotEqualToExpected()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                'bar',
            )
        );
    }

    public function testInvokeWithValueEqualToExpected()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())
            ->method('continue');

        self::assertInstanceOf(
            JumpIfNot::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                'foo',
            )
        );
    }
}

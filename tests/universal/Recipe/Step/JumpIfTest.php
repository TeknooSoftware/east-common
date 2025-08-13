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
use Stringable;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Recipe\Step\JumpIf;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(JumpIf::class)]
class JumpIfTest extends TestCase
{
    public function buildStep(): JumpIf
    {
        return new JumpIf();
    }

    public function testInvokeNoValue(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
            )
        );
    }

    public function testInvokeEmptyStringable(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                new class () implements Stringable {
                    public function __toString(): string
                    {
                        return '';
                    }
                }
            )
        );
    }

    public function testInvokeWithValueWithoutExpected(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo'
            )
        );
    }

    public function testInvokeWithValueWithoutExpectedAndStringable(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                new class () implements Stringable {
                    public function __toString(): string
                    {
                        return 'foo';
                    }
                }
            )
        );
    }

    public function testInvokeValueNotEqualToExpected(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                'bar',
            )
        );
    }

    public function testInvokeWithValueEqualToExpected(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                'foo',
            )
        );
    }

    public function testInvokeValueExpectedIsCallbackWithSuccess(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                fn ($val): bool => $val !== 'foo',
            )
        );
    }

    public function testInvokeValueExpectedIsCallbackWithFailure(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('continue');

        $this->assertInstanceOf(
            JumpIf::class,
            $this->buildStep()(
                $manager,
                'nextRouteName',
                'foo',
                fn ($val): bool => $val === 'foo',
            )
        );
    }
}

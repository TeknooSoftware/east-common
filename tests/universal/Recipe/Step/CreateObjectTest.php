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
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CreateObject::class)]
class CreateObjectTest extends TestCase
{
    public function buildStep(): CreateObject
    {
        return new CreateObject();
    }

    public function testInvokeBadClassName(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            \DateTimeInterface::class,
            new \stdClass()
        );
    }

    public function testInvokeClassNotExist(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        $this->assertInstanceOf(
            CreateObject::class,
            $this->buildStep()('foo', $manager)
        );
    }

    public function testInvokeClassNotObject(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        $this->assertInstanceOf(
            CreateObject::class,
            $this->buildStep()(\DateTime::class, $manager)
        );
    }

    public function testInvoke(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class () implements ObjectInterface {})::class, $manager)
        );
    }

    public function testInvokeWithArgs(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class () implements ObjectInterface {})::class, $manager, ['foo', 'bar'])
        );
    }

    public function testInvokeWithSignleArg(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan');
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class () implements ObjectInterface {})::class, $manager, 'foo', 'bar')
        );
    }
}

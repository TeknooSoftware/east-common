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
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Recipe\Step\CreateObject
 */
class CreateObjectTest extends TestCase
{
    public function buildStep(): CreateObject
    {
        return new CreateObject();
    }

    public function testInvokeBadClassName()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            \DateTimeInterface::class,
            new \stdClass()
        );
    }

    public function testInvokeClassNotExist()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()('foo', $manager)
        );
    }

    public function testInvokeClassNotObject()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()(\DateTime::class, $manager)
        );
    }

    public function testInvoke()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class implements ObjectInterface{})::class, $manager)
        );
    }

    public function testInvokeWithArgs()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class implements ObjectInterface{})::class, $manager, ['foo', 'bar'])
        );
    }

    public function testInvokeWithSignleArg()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()((new class implements ObjectInterface{})::class, $manager, 'foo', 'bar')
        );
    }
}

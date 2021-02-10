<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Recipe\Step\CreateObject;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\CreateObject
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
            $this->buildStep()(Type::class, $manager)
        );
    }

    public function testInvokeWithArgs()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            CreateObject::class,
            $this->buildStep()(Type::class, $manager, ['foo', 'bar'])
        );
    }
}

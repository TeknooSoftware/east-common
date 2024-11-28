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
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Direction::class)]
#[CoversClass(ExtractOrder::class)]
class ExtractOrderTest extends TestCase
{
    public function buildStep(): ExtractOrder
    {
        return new ExtractOrder();
    }

    public function testInvokeBadRequest()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ManagerInterface::class),
            Direction::Asc,
            'id'
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            Direction::Asc,
            'id'
        );
    }

    public function testInvokeBadDefaultOrder()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeBadDefaultDefault()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class),
            Direction::Asc,
            new \stdClass()
        );
    }

    public function testInvokeWithNoParameter()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['id' => Direction::Desc]]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $this->createMock(ServerRequestInterface::class),
                $manager
            )
        );
    }

    public function testInvokeWithDefaultToAsc()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $this->createMock(ServerRequestInterface::class),
                $manager,
                Direction::Asc,
                'createdAt'
            )
        );
    }

    public function testInvokeWithParameter()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'ASC'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Desc,
                'id'
            )
        );
    }

    public function testInvokeWithInvalidParameter()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'Foo'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        self::assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Desc,
                'id'
            )
        );
    }
}

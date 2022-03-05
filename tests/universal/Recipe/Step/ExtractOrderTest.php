<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Query\Enum\Direction;
use Teknoo\East\Website\Recipe\Step\ExtractOrder;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\ExtractOrder
 */
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
        $manager->expects(self::once())->method('updateWorkPlan')->with(['order' => ['id' => Direction::Desc]]);
        $manager->expects(self::never())->method('error');

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
        $manager->expects(self::once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects(self::never())->method('error');

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
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'ASC'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects(self::never())->method('error');

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
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'Foo'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error');

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

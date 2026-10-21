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
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ExtractOrder::class)]
class ExtractOrderTest extends TestCase
{
    public function buildStep(): ExtractOrder
    {
        return new ExtractOrder();
    }

    public function testInvokeBadRequest(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createStub(ManagerInterface::class),
            Direction::Asc,
            'id'
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            new \stdClass(),
            Direction::Asc,
            'id'
        );
    }

    public function testInvokeBadDefaultOrder(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(ManagerInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeBadDefaultDefault(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(ManagerInterface::class),
            Direction::Asc,
            new \stdClass()
        );
    }

    public function testInvokeWithNoParameter(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['id' => Direction::Desc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $this->createStub(ServerRequestInterface::class),
                $manager
            )
        );
    }

    public function testInvokeWithDefaultToAsc(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $this->createStub(ServerRequestInterface::class),
                $manager,
                Direction::Asc,
                'createdAt'
            )
        );
    }

    public function testInvokeWithParameter(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'ASC'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Desc,
                'id'
            )
        );
    }

    public function testInvokeWithInvalidParameter(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([
            'order' => 'createdAt',
            'direction' => 'Foo'
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Desc,
                'id'
            )
        );
    }

    public function testInvokeWithParameterInAttributes(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getAttribute')->willReturnCallback(
            static fn (string $name): ?string => match ($name) {
                'order' => 'createdAt',
                'direction' => 'asc',
                default => null,
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Desc,
                'id'
            )
        );
    }

    public function testInvokeWithParameterInAttributesPreferredToQuery(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([
            'order' => 'name',
            'direction' => 'DESC'
        ]);
        $request->method('getAttribute')->willReturnCallback(
            static fn (string $name): ?string => match ($name) {
                'order' => 'createdAt',
                'direction' => 'ASC',
                default => null,
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['createdAt' => Direction::Asc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Asc,
                'id'
            )
        );
    }

    public function testInvokeWithOnlyDirectionInAttributes(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([
            'order' => 'name',
            'direction' => 'ASC'
        ]);
        $request->method('getAttribute')->willReturnCallback(
            static fn (string $name): ?string => match ($name) {
                'direction' => 'DESC',
                default => null,
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['order' => ['name' => Direction::Desc]]);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            ExtractOrder::class,
            $this->buildStep()(
                $request,
                $manager,
                Direction::Asc,
                'id'
            )
        );
    }

    public function testInvokeWithInvalidParameterInAttributes(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getAttribute')->willReturnCallback(
            static fn (string $name): ?string => match ($name) {
                'order' => 'createdAt',
                'direction' => 'Foo',
                default => null,
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        $this->assertInstanceOf(
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

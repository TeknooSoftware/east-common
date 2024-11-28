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
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LoadListObjects::class)]
class LoadListObjectsTest extends TestCase
{
    public function buildStep(): LoadListObjects
    {
        return new LoadListObjects();
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ManagerInterface::class),
            [],
            10,
            1
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            [],
            10,
            1
        );
    }

    public function testInvokeBadOrder()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            $this->createMock(ManagerInterface::class),
            new \stdClass(),
            10,
            1
        );
    }

    public function testInvokeBadItemsParPage()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            $this->createMock(ManagerInterface::class),
            [],
            new \stdClass(),
            1
        );
    }

    public function testInvokeBadPage()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            $this->createMock(ManagerInterface::class),
            [],
            10,
            new \stdClass()
        );
    }

    public function testInvokeFoundWithNoCountable()
    {
        $objects = new class($this->createMock(...)) implements \IteratorAggregate {
            private $createMock;

            public function __construct(
                callable $createMock,
            ) {
                $this->createMock = $createMock;
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([
                    ($this->createMock)(IdentifiedObjectInterface::class),
                    ($this->createMock)(IdentifiedObjectInterface::class),
                ]);
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'objectsCollection' => $objects,
            'pageCount' => 1
        ]);

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2
            )
        );
    }

    public function testInvokeFoundWithCountable()
    {
        $pageCount = 3;
        $objects = new class implements \Countable, \IteratorAggregate {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }

            public function count(): int
            {
                return 30;
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'objectsCollection' => $objects,
            'pageCount' => $pageCount
        ]);

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2
            )
        );
    }

    public function testInvokeFoundWithCountableAndCriteria()
    {
        $pageCount = 3;
        $objects = new class implements \Countable, \IteratorAggregate {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }

            public function count(): int
            {
                return 30;
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'objectsCollection' => $objects,
            'pageCount' => $pageCount
        ]);

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => 'bar',
                    'ba123' => 'Richard Déloge'
                ]
            )
        );
    }

    public function testInvokeFoundWithCountableAndCriteriaAsExpr()
    {
        $pageCount = 3;
        $objects = new class implements \Countable, \IteratorAggregate {
            public function getIterator(): \Traversable
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }

            public function count(): int
            {
                return 30;
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'objectsCollection' => $objects,
            'pageCount' => $pageCount
        ]);

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => 'bar',
                    'ba123' => $this->createMock(ExprInterface::class)
                ]
            )
        );
    }

    public function testInvokeErrorInQuery()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($loader) {
                $promise->fail(new \RuntimeException('Error'));

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaKeyName()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    '@foo' => 'bar'
                ]
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaKeyType()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    0 => 'bar'
                ]
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaValue()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => "bar[do"
                ]
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaValueType()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => ['bar']
                ]
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaValueTypeObject()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => new \stdClass
                ]
            )
        );
    }
}

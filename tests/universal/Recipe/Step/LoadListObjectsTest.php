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
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Recipe\Step\LoadListObjects;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\LoadListObjects
 */
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
        $objects = new class implements \IteratorAggregate {
            public function getIterator()
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
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
            public function getIterator()
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }

            public function count()
            {
                return 30;
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
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
            public function getIterator()
            {
                return new \ArrayIterator([
                    new Content(),
                    new Content()
                ]);
            }

            public function count()
            {
                return 30;
            }
        };

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($objects, $loader) {
                $promise->success($objects);

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
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

    public function testInvokeErrorInQuery()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())->method('query')->willReturnCallback(
            function ($query, PromiseInterface $promise) use ($loader) {
                $promise->fail(new \RuntimeException('Error'));

                return $loader;
            }
        );

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $loader->expects(self::never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $loader->expects(self::never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

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
        $loader->expects(self::never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

        self::assertInstanceOf(
            LoadListObjects::class,
            $this->buildStep()(
                $loader,
                $manager,
                ['foo' => 'ASC'],
                10,
                2,
                [
                    'foo' => "bar;do"
                ]
            )
        );
    }

    public function testInvokeErrorWithBadCriteriaValueType()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::never())->method('query');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

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
}

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
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\LoadObject;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\LoadObject
 */
class LoadObjectTest extends TestCase
{
    public function buildStep(): LoadObject
    {
        return new LoadObject();
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            "123",
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadId()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            "123",
            new \stdClass()
        );
    }

    public function testInvokeFound()
    {
        $object = $this->createMock(ObjectInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
            ObjectInterface::class => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader, $object) {
                    $promise->success($object);

                    return $loader;
                }
            );

        self::assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager
            )
        );
    }

    public function testInvokeFoundWithKey()
    {
        $object = $this->createMock(ObjectInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
            'MyKey' => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader, $object) {
                    $promise->success($object);

                    return $loader;
                }
            );

        self::assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager,
                'MyKey'
            )
        );
    }

    public function testInvokeError()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error')->with(
            new \DomainException('foo', 404, new \DomainException('foo'))
        );
        $manager->expects(self::never())->method('updateWorkPlan');

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader) {
                    $promise->fail(new \DomainException('foo'));

                    return $loader;
                }
            );

        self::assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager
            )
        );
    }
}

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
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LoadObject::class)]
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
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            ObjectInterface::class => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
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
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'MyKey' => $object
        ]);

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
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
        $manager->expects($this->once())->method('error')->with(
            new \DomainException('foo', 404, new \DomainException('foo'))
        );
        $manager->expects($this->never())->method('updateWorkPlan');

        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
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

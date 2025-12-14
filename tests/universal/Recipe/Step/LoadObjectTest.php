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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LoadObject::class)]
class LoadObjectTest extends TestCase
{
    public function buildStep(): LoadObject
    {
        return new LoadObject();
    }

    public function testInvokeBadLoader(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            "123",
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadId(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createStub(LoaderInterface::class),
            new stdClass(),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createStub(LoaderInterface::class),
            "123",
            new stdClass()
        );
    }

    public function testInvokeFound(): void
    {
        $object = $this->createStub(ObjectInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            ObjectInterface::class => $object
        ]);

        $loader = $this->createStub(LoaderInterface::class);
        $loader
            ->method('load')
            ->willReturnCallback(
                function (string $query, PromiseInterface $promise) use ($loader, $object): Stub {
                    $promise->success($object);

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager
            )
        );
    }

    public function testInvokeFoundWithKey(): void
    {
        $object = $this->createStub(ObjectInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'MyKey' => $object
        ]);

        $loader = $this->createStub(LoaderInterface::class);
        $loader
            ->method('load')
            ->willReturnCallback(
                function (string $query, PromiseInterface $promise) use ($loader, $object): Stub {
                    $promise->success($object);

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager,
                'MyKey'
            )
        );
    }

    public function testInvokeError(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error')->with(
            new DomainException('foo', 404, new DomainException('foo'))
        );
        $manager->expects($this->never())->method('updateWorkPlan');

        $loader = $this->createStub(LoaderInterface::class);
        $loader
            ->method('load')
            ->willReturnCallback(
                function (string $query, PromiseInterface $promise) use ($loader): Stub {
                    $promise->fail(new DomainException('foo'));

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            LoadObject::class,
            $this->buildStep()(
                $loader,
                'foo',
                $manager
            )
        );
    }
}

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
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface as ObjectWithId;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SaveObject::class)]
class SaveObjectTest extends TestCase
{
    public function buildStep(): SaveObject
    {
        return new SaveObject();
    }

    public function testInvokeBadWriter(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ObjectInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadObject(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(WriterInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(WriterInterface::class),
            $this->createMock(ObjectInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeWithObjectId(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectWithId::class);
        $object->method('getId')->willReturn('foo');
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'id' => 'foo',
            'parameters' => [
                'id' => 'foo',
                'objectSaved' => true
            ],
            'formHandleRequest' => false,
            'objectSaved' => true,
        ]);

        $writer
            ->method('save')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Object\ObjectInterface $object, PromiseInterface $promise) use ($writer): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success($object);

                    return $writer;
                }
            );

        $this->assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager,
                $this->createMock(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithObjectContact(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'formHandleRequest' => false,
            'objectSaved' => true,
        ]);

        $writer
            ->method('save')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Object\ObjectInterface $object, PromiseInterface $promise) use ($writer): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success($object);

                    return $writer;
                }
            );

        $this->assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager,
                $this->createMock(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithErrorWithObjectContract(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        $writer
            ->method('save')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Object\ObjectInterface $object, PromiseInterface $promise) use ($writer): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(
                        new \Exception()
                    );

                    return $writer;
                }
            );

        $this->assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager,
                $this->createMock(ParametersBag::class),
            )
        );
    }

    public function testInvokeWithErrorWithObjectId(): void
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectWithId::class);
        $object->method('getId')->willReturn('foo');
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects($this->once())->method('error');
        $manager->expects($this->never())->method('updateWorkPlan');

        $writer
            ->method('save')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Object\ObjectInterface $object, PromiseInterface $promise) use ($writer): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(
                        new \Exception()
                    );

                    return $writer;
                }
            );

        $this->assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager,
                $this->createMock(ParametersBag::class),
            )
        );
    }
}

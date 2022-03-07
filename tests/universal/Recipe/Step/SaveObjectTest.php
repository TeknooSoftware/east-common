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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Object\ObjectInterface as ObjectWithId;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\SaveObject;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\SaveObject
 */
class SaveObjectTest extends TestCase
{
    public function buildStep(): SaveObject
    {
        return new SaveObject();
    }

    public function testInvokeBadWriter()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ObjectInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadObject()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(WriterInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(WriterInterface::class),
            $this->createMock(ObjectInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeWithObjectId()
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectWithId
        ::class);
        $object->expects(self::any())->method('getId')->willReturn('foo');
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
            'id' => 'foo',
            'parameters' => [
                'id' => 'foo',
            ],
        ]);

        $writer->expects(self::any())
            ->method('save')
            ->willReturnCallback(
                function ($object, PromiseInterface $promise) use ($writer) {
                    $promise->success($object);

                    return $writer;
                }
            );

        self::assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager
            )
        );
    }

    public function testInvokeWithObjectContact()
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects(self::never())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

        $writer->expects(self::any())
            ->method('save')
            ->willReturnCallback(
                function ($object, PromiseInterface $promise) use ($writer) {
                    $promise->success($object);

                    return $writer;
                }
            );

        self::assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager
            )
        );
    }

    public function testInvokeWithErrorWithObjectContract()
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

        $writer->expects(self::any())
            ->method('save')
            ->willReturnCallback(
                function ($object, PromiseInterface $promise) use ($writer) {
                    $promise->fail(
                        new \Exception()
                    );

                    return $writer;
                }
            );

        self::assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager
            )
        );
    }

    public function testInvokeWithErrorWithObjectId()
    {
        $writer = $this->createMock(WriterInterface::class);
        $object = $this->createMock(ObjectWithId::class);
        $object->expects(self::any())->method('getId')->willReturn('foo');
        $manager = $this->createMock(ManagerInterface::class);

        $manager->expects(self::once())->method('error');
        $manager->expects(self::never())->method('updateWorkPlan');

        $writer->expects(self::any())
            ->method('save')
            ->willReturnCallback(
                function ($object, PromiseInterface $promise) use ($writer) {
                    $promise->fail(
                        new \Exception()
                    );

                    return $writer;
                }
            );

        self::assertInstanceOf(
            SaveObject::class,
            $this->buildStep()(
                $writer,
                $object,
                $manager
            )
        );
    }
}

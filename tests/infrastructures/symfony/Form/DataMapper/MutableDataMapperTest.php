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

namespace Teknoo\Tests\East\CommonBundle\Form\DataMapper;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\CommonBundle\Form\DataMapper\AbstractDataMapper;
use Teknoo\East\CommonBundle\Form\DataMapper\DataReaderTrait;
use Teknoo\East\CommonBundle\Form\DataMapper\DataWriterTrait;
use Teknoo\East\CommonBundle\Form\DataMapper\MutableDataMapper;
use Teknoo\Tests\East\Common\Support\Fixtures\MutableObject;
use Teknoo\Tests\East\Common\Support\Fixtures\MutableWithAccessorsObject;
use Teknoo\Tests\East\Common\Support\Fixtures\MutableWithReadonlyObject;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MutableDataMapper::class)]
#[CoversClass(AbstractDataMapper::class)]
#[CoversTrait(DataReaderTrait::class)]
#[CoversTrait(DataWriterTrait::class)]
class MutableDataMapperTest extends TestCase
{
    private function buildMapper(string $class): MutableDataMapper
    {
        $mapper = new MutableDataMapper();
        $mapper = $mapper->configure($class);

        return $mapper;
    }

    private function createFormMock(string $name): FormInterface&MockObject
    {
        $form = $this->createMock(FormInterface::class);
        $config = $this->createStub(FormConfigInterface::class);
        $config->method('getName')->willReturn($name);
        $form->method('getConfig')->willReturn($config);

        return $form;
    }

    private function createFormStub(string $name): FormInterface&Stub
    {
        $form = $this->createStub(FormInterface::class);
        $config = $this->createStub(FormConfigInterface::class);
        $config->method('getName')->willReturn($name);
        $form->method('getConfig')->willReturn($config);

        return $form;
    }

    public function testSupport(): void
    {
        $mapper = new MutableDataMapper();
        $this->assertTrue($mapper->support(MutableObject::class));
        $this->assertTrue($mapper->support(stdClass::class));
    }

    public function testMapDataToFormsWithPublicProperty(): void
    {
        $mapper = $this->buildMapper(MutableObject::class);
        $data = new MutableObject();
        $data->name = 'hello';

        $form = $this->createFormMock('name');
        $form->expects($this->once())->method('setData')->with('hello');

        $mapper->mapDataToForms($data, new ArrayIterator(['name' => $form]));
    }

    public function testMapDataToFormsWithGetter(): void
    {
        $mapper = $this->buildMapper(MutableWithAccessorsObject::class);
        $data = new MutableWithAccessorsObject();
        $data->setTitle('world');

        $form = $this->createFormMock('title');
        $form->expects($this->once())->method('setData')->with('world');

        $mapper->mapDataToForms($data, new ArrayIterator(['title' => $form]));
    }

    public function testMapDataToFormsWithNullData(): void
    {
        $mapper = $this->buildMapper(MutableObject::class);
        $form = $this->createFormMock('name');
        $form->expects($this->never())->method('setData');

        $mapper->mapDataToForms(null, new ArrayIterator(['name' => $form]));
    }

    public function testMapFormsToDataWithPublicProperty(): void
    {
        $mapper = $this->buildMapper(MutableObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('updated');

        $data = new MutableObject();
        $data->name = 'original';

        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertSame('updated', $data->name);
    }

    public function testMapFormsToDataWithSetter(): void
    {
        $mapper = $this->buildMapper(MutableWithAccessorsObject::class);

        $form = $this->createFormStub('title');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('updated');

        $data = new MutableWithAccessorsObject();
        $data->setTitle('original');

        $mapper->mapFormsToData(new ArrayIterator(['title' => $form]), $data);

        $this->assertSame('updated', $data->getTitle());
    }

    public function testMapFormsToDataSkipsReadonlyProperty(): void
    {
        $mapper = $this->buildMapper(MutableWithReadonlyObject::class);

        $form = $this->createFormStub('id');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn(999);

        $data = new MutableWithReadonlyObject(42);

        $mapper->mapFormsToData(new ArrayIterator(['id' => $form]), $data);

        $this->assertSame(42, $data->id);
    }

    public function testMapFormsToDataWithNullData(): void
    {
        $mapper = $this->buildMapper(MutableObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('value');

        $data = null;
        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertNull($data);
    }
}

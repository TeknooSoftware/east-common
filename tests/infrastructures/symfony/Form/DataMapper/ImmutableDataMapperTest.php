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
use Teknoo\East\CommonBundle\Form\DataMapper\ImmutableDataMapper;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableNoConstructorTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableNullableNoDefaultTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableWithGetterTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableWithHasserTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\ImmutableWithIsserTestObject;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ImmutableDataMapper::class)]
#[CoversClass(AbstractDataMapper::class)]
#[CoversTrait(DataReaderTrait::class)]
class ImmutableDataMapperTest extends TestCase
{
    private function buildMapper(string $class): ImmutableDataMapper
    {
        $mapper = new ImmutableDataMapper();
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
        $mapper = new ImmutableDataMapper();
        $this->assertTrue($mapper->support(ImmutableTestObject::class));
        $this->assertFalse($mapper->support(stdClass::class));
    }

    public function testMapDataToFormsWithNullData(): void
    {
        $mapper = $this->buildMapper(ImmutableTestObject::class);
        $form = $this->createFormMock('value');
        $form->expects($this->never())->method('setData');

        $mapper->mapDataToForms(null, new ArrayIterator(['value' => $form]));
    }

    public function testMapDataToFormsWithPublicProperty(): void
    {
        $mapper = $this->buildMapper(ImmutableTestObject::class);
        $data = new ImmutableTestObject('hello', 42);

        $form = $this->createFormMock('value');
        $form->expects($this->once())->method('setData')->with('hello');

        $mapper->mapDataToForms($data, new ArrayIterator(['value' => $form]));
    }

    public function testMapDataToFormsWithGetter(): void
    {
        $mapper = $this->buildMapper(ImmutableWithGetterTestObject::class);
        $data = new ImmutableWithGetterTestObject('hello');

        $form = $this->createFormMock('value');
        $form->expects($this->once())->method('setData')->with('hello');

        $mapper->mapDataToForms($data, new ArrayIterator(['value' => $form]));
    }

    public function testMapDataToFormsWithHasser(): void
    {
        $mapper = $this->buildMapper(ImmutableWithHasserTestObject::class);
        $data = new ImmutableWithHasserTestObject(true);

        $form = $this->createFormMock('active');
        $form->expects($this->once())->method('setData')->with(true);

        $mapper->mapDataToForms($data, new ArrayIterator(['active' => $form]));
    }

    public function testMapDataToFormsWithIsser(): void
    {
        $mapper = $this->buildMapper(ImmutableWithIsserTestObject::class);
        $data = new ImmutableWithIsserTestObject(false);

        $form = $this->createFormMock('enabled');
        $form->expects($this->once())->method('setData')->with(false);

        $mapper->mapDataToForms($data, new ArrayIterator(['enabled' => $form]));
    }

    public function testMapFormsToDataCreatesNewInstance(): void
    {
        $mapper = $this->buildMapper(ImmutableTestObject::class);

        $formValue = $this->createFormStub('value');
        $formValue->method('isSubmitted')->willReturn(true);
        $formValue->method('isDisabled')->willReturn(false);
        $formValue->method('getData')->willReturn('new_value');

        $formCount = $this->createFormStub('count');
        $formCount->method('isSubmitted')->willReturn(true);
        $formCount->method('isDisabled')->willReturn(false);
        $formCount->method('getData')->willReturn(99);

        $data = new ImmutableTestObject('old', 0);
        $mapper->mapFormsToData(
            new ArrayIterator(['value' => $formValue, 'count' => $formCount]),
            $data,
        );

        $this->assertInstanceOf(ImmutableTestObject::class, $data);
        $this->assertSame('new_value', $data->value);
        $this->assertSame(99, $data->count);
    }

    public function testMapFormsToDataUsesDefaultValueForMissingFields(): void
    {
        $mapper = $this->buildMapper(ImmutableTestObject::class);

        $formValue = $this->createFormStub('value');
        $formValue->method('isSubmitted')->willReturn(true);
        $formValue->method('isDisabled')->willReturn(false);
        $formValue->method('getData')->willReturn('submitted');

        $data = new ImmutableTestObject('old', 5);
        $mapper->mapFormsToData(
            new ArrayIterator(['value' => $formValue]),
            $data,
        );

        $this->assertInstanceOf(ImmutableTestObject::class, $data);
        $this->assertSame('submitted', $data->value);
        $this->assertSame(0, $data->count);
    }

    public function testMapFormsToDataUsesNullForMissingFieldsWithoutDefault(): void
    {
        $mapper = $this->buildMapper(ImmutableNullableNoDefaultTestObject::class);

        $formValue = $this->createFormStub('value');
        $formValue->method('isSubmitted')->willReturn(true);
        $formValue->method('isDisabled')->willReturn(false);
        $formValue->method('getData')->willReturn('submitted');

        $data = new ImmutableNullableNoDefaultTestObject('old', 42);
        $mapper->mapFormsToData(
            new ArrayIterator(['value' => $formValue]),
            $data,
        );

        $this->assertInstanceOf(ImmutableNullableNoDefaultTestObject::class, $data);
        $this->assertSame('submitted', $data->value);
        $this->assertNull($data->count);
    }

    public function testMapFormsToDataSkipsDisabledForms(): void
    {
        $mapper = $this->buildMapper(ImmutableTestObject::class);

        $formValue = $this->createFormStub('value');
        $formValue->method('isSubmitted')->willReturn(true);
        $formValue->method('isDisabled')->willReturn(false);
        $formValue->method('getData')->willReturn('submitted');

        $disabledForm = $this->createStub(FormInterface::class);
        $disabledConfig = $this->createStub(FormConfigInterface::class);
        $disabledConfig->method('getName')->willReturn('count');
        $disabledForm->method('getConfig')->willReturn($disabledConfig);
        $disabledForm->method('isSubmitted')->willReturn(true);
        $disabledForm->method('isDisabled')->willReturn(true);
        $disabledForm->method('getData')->willReturn(999);

        $data = new ImmutableTestObject('old', 5);
        $mapper->mapFormsToData(
            new ArrayIterator(['value' => $formValue, 'count' => $disabledForm]),
            $data,
        );

        $this->assertInstanceOf(ImmutableTestObject::class, $data);
        $this->assertSame('submitted', $data->value);
        $this->assertSame(0, $data->count);
    }

    public function testMapFormsToDataWithNoConstructor(): void
    {
        $mapper = $this->buildMapper(ImmutableNoConstructorTestObject::class);

        $data = null;
        $mapper->mapFormsToData(new ArrayIterator(), $data);

        $this->assertInstanceOf(ImmutableNoConstructorTestObject::class, $data);
    }
}

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
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\CommonBundle\Form\DataMapper\AbstractDataMapper;
use Teknoo\East\CommonBundle\Form\DataMapper\DataWriterTrait;
use Teknoo\East\CommonBundle\Form\DataMapper\VisitableDataMapper;
use Teknoo\Tests\East\Common\Support\Fixtures\VisitableTestObject;
use Teknoo\Tests\East\Common\Support\Fixtures\VisitableWithPublicPropTestObject;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(VisitableDataMapper::class)]
#[CoversClass(AbstractDataMapper::class)]
#[CoversTrait(DataWriterTrait::class)]
class VisitableDataMapperTest extends TestCase
{
    private function buildMapper(string $class): VisitableDataMapper
    {
        $mapper = new VisitableDataMapper();
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
        $mapper = new VisitableDataMapper();
        $this->assertTrue($mapper->support(VisitableTestObject::class));
        $this->assertFalse($mapper->support(stdClass::class));
    }

    public function testMapDataToFormsWithNullData(): void
    {
        $mapper = $this->buildMapper(VisitableTestObject::class);
        $form = $this->createFormMock('name');
        $form->expects($this->never())->method('setData');

        $mapper->mapDataToForms(null, new ArrayIterator(['name' => $form]));
    }

    public function testMapDataToFormsCallsVisit(): void
    {
        $formName = $this->createFormStub('name');
        $formTitle = $this->createFormStub('title');

        $visitCalled = false;
        $visitableData = new class ('hello', 'world', $visitCalled) extends VisitableTestObject {
            public function __construct(
                string $name,
                string $title,
                private bool &$visitCalled,
            ) {
                parent::__construct($name, $title);
            }

            public function visit(string|array $visitors, ?callable $callable = null): VisitableInterface
            {
                $this->visitCalled = true;
                Assert::assertIsArray($visitors);
                Assert::assertArrayHasKey('name', $visitors);
                Assert::assertArrayHasKey('title', $visitors);

                return $this;
            }
        };

        $mapper = $this->buildMapper($visitableData::class);
        $mapper->mapDataToForms($visitableData, new ArrayIterator([
            'name' => $formName,
            'title' => $formTitle,
        ]));

        $this->assertTrue($visitCalled);
    }

    public function testMapDataToFormsWithWrongType(): void
    {
        $mapper = $this->buildMapper(VisitableTestObject::class);

        $form = $this->createFormMock('name');
        $form->expects($this->never())->method('setData');

        $mapper->mapDataToForms(new stdClass(), new ArrayIterator(['name' => $form]));
    }

    public function testMapFormsToDataWithSetter(): void
    {
        $mapper = $this->buildMapper(VisitableTestObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('updated');

        $data = new VisitableTestObject('original', 'title');
        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertSame('updated', $data->getName());
    }

    public function testMapFormsToDataSkipsNotSubmittedForm(): void
    {
        $mapper = $this->buildMapper(VisitableTestObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('should_not_apply');

        $data = new VisitableTestObject('original', 'title');
        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertSame('original', $data->getName());
    }

    public function testMapFormsToDataSkipsDisabledForm(): void
    {
        $mapper = $this->buildMapper(VisitableTestObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(true);
        $form->method('getData')->willReturn('should_not_apply');

        $data = new VisitableTestObject('original', 'title');
        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertSame('original', $data->getName());
    }

    public function testMapFormsToDataWithPublicProperty(): void
    {
        $mapper = $this->buildMapper(VisitableWithPublicPropTestObject::class);

        $form = $this->createFormStub('name');
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isDisabled')->willReturn(false);
        $form->method('getData')->willReturn('updated');

        $data = new VisitableWithPublicPropTestObject();
        $data->name = 'original';
        $mapper->mapFormsToData(new ArrayIterator(['name' => $form]), $data);

        $this->assertSame('updated', $data->name);
    }
}

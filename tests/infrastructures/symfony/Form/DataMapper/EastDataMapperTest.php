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
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Teknoo\East\CommonBundle\Form\DataMapper\AbstractDataMapper;
use Teknoo\East\CommonBundle\Form\DataMapper\EastDataMapper;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(EastDataMapper::class)]
class EastDataMapperTest extends TestCase
{
    public function testConfigureSelectsFirstSupportingMapper(): void
    {
        $mapper1 = $this->createStub(AbstractDataMapper::class);
        $mapper1->method('support')->willReturn(false);

        $mapper2 = $this->createMock(AbstractDataMapper::class);
        $mapper2->expects($this->once())
            ->method('support')
            ->with('SomeClass')
            ->willReturn(true);
        $mapper2->expects($this->once())
            ->method('configure')
            ->with('SomeClass')
            ->willReturnSelf();

        $eastMapper = new EastDataMapper([$mapper1, $mapper2]);
        $result = $eastMapper->configure('SomeClass');

        $this->assertInstanceOf(EastDataMapper::class, $result);
    }

    public function testConfigureSkipsNonSupportingMappers(): void
    {
        $mapper1 = $this->createMock(AbstractDataMapper::class);
        $mapper1->expects($this->once())
            ->method('support')
            ->willReturn(false);
        $mapper1->expects($this->never())
            ->method('configure');

        $mapper2 = $this->createStub(AbstractDataMapper::class);
        $mapper2->method('support')->willReturn(true);
        $mapper2->method('configure')->willReturnSelf();

        $eastMapper = new EastDataMapper([$mapper1, $mapper2]);
        $eastMapper->configure('SomeClass');

        $this->assertTrue(true);
    }

    public function testConfigureThrowsWhenNoMapperSupports(): void
    {
        $mapper = $this->createStub(AbstractDataMapper::class);
        $mapper->method('support')->willReturn(false);

        $eastMapper = new EastDataMapper([$mapper]);

        $this->expectException(LogicException::class);
        $eastMapper->configure('UnsupportedClass');
    }

    public function testConfigureThrowsWithEmptyMappers(): void
    {
        $eastMapper = new EastDataMapper([]);

        $this->expectException(LogicException::class);
        $eastMapper->configure('AnyClass');
    }

    public function testMapDataToFormsWithoutConfigureThrowsException(): void
    {
        $eastMapper = new EastDataMapper([]);

        $this->expectException(LogicException::class);
        $eastMapper->mapDataToForms(null, new ArrayIterator());
    }

    public function testMapFormsToDataWithoutConfigureThrowsException(): void
    {
        $eastMapper = new EastDataMapper([]);

        $this->expectException(LogicException::class);
        $data = null;
        $eastMapper->mapFormsToData(new ArrayIterator(), $data);
    }

    public function testMapDataToFormsDelegatesToCurrentMapper(): void
    {
        $subMapper = $this->createMock(AbstractDataMapper::class);

        $viewData = new \stdClass();
        $forms = new ArrayIterator();

        $subMapper->expects($this->once())
            ->method('mapDataToForms')
            ->with($viewData, $forms);

        $eastMapper = new EastDataMapper([]);
        $ref = new ReflectionProperty($eastMapper, 'current');
        $ref->setValue($eastMapper, $subMapper);

        $eastMapper->mapDataToForms($viewData, $forms);
    }

    public function testMapFormsToDataDelegatesToCurrentMapper(): void
    {
        $subMapper = $this->createMock(AbstractDataMapper::class);

        $forms = new ArrayIterator();
        $viewData = new \stdClass();

        $subMapper->expects($this->once())
            ->method('mapFormsToData')
            ->with($forms, $viewData);

        $eastMapper = new EastDataMapper([]);
        $ref = new ReflectionProperty($eastMapper, 'current');
        $ref->setValue($eastMapper, $subMapper);

        $eastMapper->mapFormsToData($forms, $viewData);
    }
}

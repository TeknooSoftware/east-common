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

namespace Teknoo\Tests\East\CommonBundle\Rendering;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\LiveComponent\Metadata\LiveComponentMetadata;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Symfony\UX\TwigComponent\ComponentMetadata;
use Teknoo\East\CommonBundle\Contracts\Rendering\ComponentFactoryInterface;
use Teknoo\East\CommonBundle\Contracts\Rendering\LiveComponentHydratorInterface;
use Teknoo\East\CommonBundle\Contracts\Rendering\LiveComponentMetadataFactoryInterface;
use Teknoo\East\CommonBundle\Rendering\LiveComponentBuilder;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LiveComponentBuilder::class)]
class LiveComponentBuilderTest extends TestCase
{
    private function createWithoutConstructor(string $className): object
    {
        $reflection = new ReflectionClass($className);
        return $reflection->newInstanceWithoutConstructor();
    }

    private function createComponentMetadata(string $serviceId, string $class, string $defaultAction = '__invoke'): ComponentMetadata
    {
        return new ComponentMetadata([
            'service_id' => $serviceId,
            'class' => $class,
            'default_action' => $defaultAction,
        ]);
    }

    public function testBuildComponentReturnsFalseWhenComponentFactoryIsNull(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $container = $this->createStub(ContainerInterface::class);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: null,
            hydrator: null,
            metadataFactory: null,
        );

        $this->assertFalse(
            $builder->buildComponent(
                $request,
                'TestComponent',
                [],
                []
            )
        );
    }

    public function testBuildComponentReturnsFalseWhenHydratorIsNull(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $container = $this->createStub(ContainerInterface::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: null,
            metadataFactory: null,
        );

        $this->assertFalse(
            $builder->buildComponent(
                $request,
                'TestComponent',
                [],
                []
            )
        );
    }

    public function testBuildComponentReturnsFalseWhenMetadataFactoryIsNull(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $container = $this->createStub(ContainerInterface::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: null,
        );

        $this->assertFalse(
            $builder->buildComponent(
                $request,
                'TestComponent',
                [],
                []
            )
        );
    }

    public function testBuildComponentObjectFromContainer(): void
    {
        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')
            ->with('service_id')
            ->willReturn(true);
        $container->expects($this->once())
            ->method('get')
            ->with('service_id')
            ->willReturn($component);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testBuildComponentObjectFromContainerNotObject(): void
    {
        $componentClass = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn('not_an_object');

        $metadata = $this->createComponentMetadata('service_id', $componentClass::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testBuildComponentObjectDirectInstantiation(): void
    {
        $componentClass = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $componentClass::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testHydrateComponentObjectWithAllData(): void
    {
        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $liveMetadata = $this->createWithoutConstructor(LiveComponentMetadata::class);

        $hydrator = $this->createMock(LiveComponentHydratorInterface::class);
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with(
                $this->isInstanceOf($component::class),
                ['prop1' => 'value1'],
                ['updated1' => 'value2'],
                $liveMetadata,
                ['parent1' => 'value3']
            )
            ->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn($liveMetadata);

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent(
                $request,
                'TestComponent',
                [],
                [
                    'props' => ['prop1' => 'value1'],
                    'updated' => ['updated1' => 'value2'],
                    'children' => ['child1' => 'data1'],
                    'propsFromParent' => ['parent1' => 'value3'],
                ]
            )
        );
    }

    public function testHydrateComponentObjectWithEmptyData(): void
    {
        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $liveMetadata = $this->createWithoutConstructor(LiveComponentMetadata::class);

        $hydrator = $this->createMock(LiveComponentHydratorInterface::class);
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with(
                $this->isInstanceOf($component::class),
                [],
                [],
                $liveMetadata,
                []
            )
            ->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn($liveMetadata);

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testUpdateComponentObjectExistingProperties(): void
    {
        $component = new class {
            public string $prop1 = '';
            public string $prop2 = '';
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($component);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent(
                $request,
                'TestComponent',
                ['prop1' => 'updated1', 'prop2' => 'updated2'],
                []
            )
        );

        $this->assertEquals('updated1', $component->prop1);
        $this->assertEquals('updated2', $component->prop2);
    }

    public function testUpdateComponentObjectIgnoreNonExisting(): void
    {
        $component = new class {
            public string $existingProp = '';
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($component);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent(
                $request,
                'TestComponent',
                ['existingProp' => 'value1', 'nonExisting' => 'value2'],
                []
            )
        );

        $this->assertEquals('value1', $component->existingProp);
        $this->assertFalse(property_exists($component, 'nonExisting'));
    }

    public function testGetComponentControllerDefaultInvoke(): void
    {
        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class, '__invoke');

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testGetComponentControllerCustomActionWithParentheses(): void
    {
        $component = new class {
            public function customAction(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class, 'customAction()');

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();
        

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testGetComponentControllerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build LiveComponent object.');

        $component = new class {
            // No callable method
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class, '__invoke');

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $builder->buildComponent($request, 'TestComponent', [], []);
    }

    public function testGetSymfonyRequestSuccess(): void
    {
        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $sfRequest = new Request();

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getAttribute')
            ->with('request')
            ->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $this->assertTrue(
            $builder->buildComponent($request, 'TestComponent', [], [])
        );
    }

    public function testGetSymfonyRequestThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build LiveComponent object.');

        $component = new class {
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willReturn($metadata);

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->method('getMetadata')->willReturn(
            $this->createWithoutConstructor(LiveComponentMetadata::class)
        );

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn(null);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $builder->buildComponent($request, 'TestComponent', [], []);
    }

    public function testBuildComponentWrapsExceptionInRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build LiveComponent object.');

        $container = $this->createStub(ContainerInterface::class);

        $factory = $this->createStub(ComponentFactoryInterface::class);
        $factory->method('metadataFor')->willThrowException(new Exception('Test exception'));

        $hydrator = $this->createStub(LiveComponentHydratorInterface::class);
        $metadataFactory = $this->createStub(LiveComponentMetadataFactoryInterface::class);

        $request = $this->createStub(ServerRequestInterface::class);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $builder->buildComponent($request, 'TestComponent', [], []);
    }

    public function testBuildComponentFullFlowSuccess(): void
    {
        $component = new class {
            public string $param1 = '';
            public function __invoke(): void {}
        };

        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($component);

        $metadata = $this->createComponentMetadata('service_id', $component::class);

        $factory = $this->createMock(ComponentFactoryInterface::class);
        $factory->expects($this->once())
            ->method('metadataFor')
            ->with('TestComponent')
            ->willReturn($metadata);

        $liveMetadata = $this->createWithoutConstructor(LiveComponentMetadata::class);

        $hydrator = $this->createMock(LiveComponentHydratorInterface::class);
        $hydrator->expects($this->once())
            ->method('hydrate')
            ->willReturn($this->createWithoutConstructor(ComponentAttributes::class));

        $metadataFactory = $this->createMock(LiveComponentMetadataFactoryInterface::class);
        $metadataFactory->expects($this->once())
            ->method('getMetadata')
            ->with('TestComponent')
            ->willReturn($liveMetadata);

        $sfRequest = new Request();

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn($sfRequest);

        $builder = new LiveComponentBuilder(
            container: $container,
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: $metadataFactory,
        );

        $result = $builder->buildComponent(
            $request,
            'TestComponent',
            ['param1' => 'test_value'],
            [
                'props' => ['prop1' => 'value1'],
                'updated' => ['field1' => 'updated1'],
                'children' => ['child1' => 'data1'],
                'propsFromParent' => ['parent1' => 'parentValue'],
            ]
        );

        $this->assertTrue($result);
        $this->assertEquals('test_value', $component->param1);
    }
}

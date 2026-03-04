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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
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

        $factory = $this->createWithoutConstructor(\Symfony\UX\TwigComponent\ComponentFactory::class);

        $builder = new LiveComponentBuilder(
            componentFactory: $factory,
            hydrator: null,
            metadataFactory: null,
            container: $container,
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

        $factory = $this->createWithoutConstructor(\Symfony\UX\TwigComponent\ComponentFactory::class);
        $hydrator = $this->createWithoutConstructor(\Symfony\UX\LiveComponent\LiveComponentHydrator::class);

        $builder = new LiveComponentBuilder(
            componentFactory: $factory,
            hydrator: $hydrator,
            metadataFactory: null,
            container: $container,
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
}

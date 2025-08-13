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

namespace Teknoo\Tests\East\Common\Flysystem;

use DI\Container;
use DI\ContainerBuilder;
use DomainException;
use League\Flysystem\FilesystemAdapter;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Flysystem\FrontAsset\Persister;
use Teknoo\East\Common\Flysystem\FrontAsset\SourceLoader;
use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader as SourceLoaderExtension;

use function sys_get_temp_dir;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/flysystem/di.php');

        return $containerDefinition->build();
    }

    public function testFlysystemAdapter(): void
    {
        $container = $this->buildContainer();
        $callable = $container->get('teknoo.east.common.assets.flysystem.adapter');
        $this->assertIsCallable($callable);
        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $callable(sys_get_temp_dir())
        );
    }

    public function testPersisterInterfaceCssMissingPath(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(PersisterInterface::class . ':css');
    }

    public function testPersisterInterfaceCss(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set('teknoo.east.common.assets.destination.css.path', 'foo');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->assertInstanceOf(
            PersisterInterface::class,
            $container->get(PersisterInterface::class . ':css')
        );

        $this->assertInstanceOf(
            PersisterInterface::class,
            $container->get(Persister::class . ':css')
        );
    }

    public function testPersisterInterfaceJsMissingPath(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(PersisterInterface::class . ':js');
    }

    public function testPersisterInterfaceJs(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set('teknoo.east.common.assets.destination.js.path', 'foo');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->assertInstanceOf(
            PersisterInterface::class,
            $container->get(PersisterInterface::class . ':js')
        );

        $this->assertInstanceOf(
            PersisterInterface::class,
            $container->get(Persister::class . ':js')
        );
    }

    public function testSourceLoaderInterfaceCssMissingPath(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':css');
    }

    public function testSourceLoaderInterfaceCssMissingSet(): void
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.css.path', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':css');
    }

    public function testSourceLoaderInterfaceCss(): void
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.css.path', '/bar');
        $container->set('teknoo.east.common.assets.sets.css', []);
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );
        $container->set(
            SourceLoaderExtension::class,
            $this->createMock(SourceLoaderExtension::class),
        );

        $this->assertInstanceOf(
            SourceLoaderInterface::class,
            $container->get(SourceLoaderInterface::class . ':css')
        );

        $this->assertInstanceOf(
            SourceLoader::class,
            $container->get(SourceLoader::class . ':css')
        );
    }

    public function testSourceLoaderInterfaceJsMissingPath(): void
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':js');
    }

    public function testSourceLoaderInterfaceJsMissingSet(): void
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.js.path', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':js');
    }

    public function testSourceLoaderInterfaceJs(): void
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.js.path', '/bar');
        $container->set('teknoo.east.common.assets.sets.js', []);
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn (): \Closure => (fn (): \PHPUnit\Framework\MockObject\MockObject => $this->createMock(FilesystemAdapter::class)),
        );
        $container->set(
            SourceLoaderExtension::class,
            $this->createMock(SourceLoaderExtension::class),
        );

        $this->assertInstanceOf(
            SourceLoaderInterface::class,
            $container->get(SourceLoaderInterface::class . ':js')
        );

        $this->assertInstanceOf(
            SourceLoader::class,
            $container->get(SourceLoader::class . ':js')
        );
    }
}

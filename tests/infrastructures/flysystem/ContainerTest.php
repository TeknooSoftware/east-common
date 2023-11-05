<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
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

use function sys_get_temp_dir;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    /**
     * @return Container
     * @throws \Exception
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/flysystem/di.php');

        return $containerDefinition->build();
    }

    public function testFlysystemAdapter()
    {
        $container = $this->buildContainer();
        $callable = $container->get('teknoo.east.common.assets.flysystem.adapter');
        self::assertIsCallable($callable);
        self::assertInstanceOf(
            FilesystemAdapter::class,
            $callable(sys_get_temp_dir())
        );
    }

    public function testPersisterInterfaceCssMissingPath()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(PersisterInterface::class . ':css');
    }

    public function testPersisterInterfaceCss()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set('teknoo.east.common.assets.destination.css.path', 'foo');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        self::assertInstanceOf(
            PersisterInterface::class,
            $container->get(PersisterInterface::class . ':css')
        );

        self::assertInstanceOf(
            PersisterInterface::class,
            $container->get(Persister::class . ':css')
        );
    }

    public function testPersisterInterfaceJsMissingPath()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(PersisterInterface::class . ':js');
    }

    public function testPersisterInterfaceJs()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set('teknoo.east.common.assets.destination.js.path', 'foo');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        self::assertInstanceOf(
            PersisterInterface::class,
            $container->get(PersisterInterface::class . ':js')
        );

        self::assertInstanceOf(
            PersisterInterface::class,
            $container->get(Persister::class . ':js')
        );
    }

    public function testSourceLoaderInterfaceCssMissingPath()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':css');
    }

    public function testSourceLoaderInterfaceCssMissingSet()
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.css.path', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':css');
    }

    public function testSourceLoaderInterfaceCss()
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.css.path', '/bar');
        $container->set('teknoo.east.common.assets.sets.css', []);
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        self::assertInstanceOf(
            SourceLoaderInterface::class,
            $container->get(SourceLoaderInterface::class . ':css')
        );

        self::assertInstanceOf(
            SourceLoader::class,
            $container->get(SourceLoader::class . ':css')
        );
    }

    public function testSourceLoaderInterfaceJsMissingPath()
    {
        $container = $this->buildContainer();
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':js');
    }

    public function testSourceLoaderInterfaceJsMissingSet()
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.js.path', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        $this->expectException(DomainException::class);
        $container->get(SourceLoaderInterface::class . ':js');
    }

    public function testSourceLoaderInterfaceJs()
    {
        $container = $this->buildContainer();
        $container->set('teknoo.east.common.assets.source.js.path', '/bar');
        $container->set('teknoo.east.common.assets.sets.js', []);
        $container->set('kernel.project_dir', '/bar');
        $container->set(
            'teknoo.east.common.assets.flysystem.adapter',
            fn () => (fn () => $this->createMock(FilesystemAdapter::class)),
        );

        self::assertInstanceOf(
            SourceLoaderInterface::class,
            $container->get(SourceLoaderInterface::class . ':js')
        );

        self::assertInstanceOf(
            SourceLoader::class,
            $container->get(SourceLoader::class . ':js')
        );
    }
}

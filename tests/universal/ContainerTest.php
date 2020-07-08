<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website;

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\DBSource\ManagerInterface as DbManagerInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/universal/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        return $containerDefinition->build();
    }

    private function generateTestForLoader(string $className, string $repositoryInterface)
    {
        $container = $this->buildContainer();
        $repository = $this->createMock($repositoryInterface);

        $container->set($repositoryInterface, $repository);
        $loader = $container->get($className);

        self::assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testItemLoader()
    {
        $this->generateTestForLoader(ItemLoader::class, ItemRepositoryInterface::class);
    }

    public function testContentLoader()
    {
        $this->generateTestForLoader(ContentLoader::class, ContentRepositoryInterface::class);
    }

    public function testMediaLoader()
    {
        $this->generateTestForLoader(MediaLoader::class, MediaRepositoryInterface::class);
    }

    public function testTypeLoader()
    {
        $this->generateTestForLoader(TypeLoader::class, TypeRepositoryInterface::class);
    }

    public function testUserLoader()
    {
        $this->generateTestForLoader(UserLoader::class, UserRepositoryInterface::class);
    }

    private function generateTestForWriter(string $className)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($className);

        self::assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testItemWriter()
    {
        $this->generateTestForWriter(ItemWriter::class);
    }

    public function testContentWriter()
    {
        $this->generateTestForWriter(ContentWriter::class);
    }

    public function testMediaWriter()
    {
        $this->generateTestForWriter(MediaWriter::class);
    }

    public function testTypeWriter()
    {
        $this->generateTestForWriter(TypeWriter::class);
    }

    public function testUserWriter()
    {
        $this->generateTestForWriter(UserWriter::class);
    }

    private function generateTestForDelete(string $key)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($key);

        self::assertInstanceOf(
            DeletingService::class,
            $loader
        );
    }

    public function testItemDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.item');
    }

    public function testContentDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.content');
    }

    public function testMediaDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.media');
    }

    public function testTypeDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.type');
    }

    public function testUserDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.user');
    }

    public function testMenuGenerator()
    {
        $container = $this->buildContainer();
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));
        $loader = $container->get(MenuGenerator::class);

        self::assertInstanceOf(
            MenuGenerator::class,
            $loader
        );
    }

    public function testMenuMiddleware()
    {
        $container = $this->buildContainer();
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));
        $loader = $container->get(MenuMiddleware::class);

        self::assertInstanceOf(
            MenuMiddleware::class,
            $loader
        );
    }

    public function testEastManagerMiddlewareInjection()
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/universal/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        $container = $containerDefinition->build();

        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));

        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        self::assertInstanceOf(
            Manager::class,
            $manager1
        );

        self::assertInstanceOf(
            Manager::class,
            $manager2
        );

        self::assertSame($manager1, $manager2);
    }
}

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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Gedmo\Translatable\TranslatableListener;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Queue\Queue;
use Teknoo\East\Foundation\Manager\Queue\QueueInterface;
use Teknoo\East\Foundation\Processor\Processor;
use Teknoo\East\Foundation\Processor\ProcessorInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        return include __DIR__.'/../../src/universal/generator.php';
    }

    private function generateTestForLoader(string $className)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $loader = $container->get($className);

        self::assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testItemLoader()
    {
        $this->generateTestForLoader(ItemLoader::class);
    }

    public function testContentLoader()
    {
        $this->generateTestForLoader(ContentLoader::class);
    }

    public function testMediaLoader()
    {
        $this->generateTestForLoader(MediaLoader::class);
    }

    public function testTypeLoader()
    {
        $this->generateTestForLoader(TypeLoader::class);
    }

    public function testUserLoader()
    {
        $this->generateTestForLoader(UserLoader::class);
    }

    private function generateTestForWriter(string $className)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);

        $container->set(ObjectManager::class, $objectManager);
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
        $objectManager = $this->createMock(ObjectManager::class);

        $container->set(ObjectManager::class, $objectManager);
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
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $loader = $container->get(MenuGenerator::class);

        self::assertInstanceOf(
            MenuGenerator::class,
            $loader
        );
    }

    public function testMenuMiddleware()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $loader = $container->get(MenuMiddleware::class);

        self::assertInstanceOf(
            MenuMiddleware::class,
            $loader
        );
    }

    public function testLocaleMiddlewareWithSymfonyContext()
    {
        $container = $this->buildContainer();
        $translatableListener = $this->createMock(TranslatableListener::class);

        $container->set('stof_doctrine_extensions.listener.translatable', $translatableListener);
        $loader = $container->get(LocaleMiddleware::class);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $loader
        );
    }

    public function testLocaleMiddlewareWithoutSymfonyContext()
    {
        $container = $this->buildContainer();
        $translatableListener = $this->createMock(TranslatableListener::class);

        $container->set(TranslatableListener::class, $translatableListener);
        $loader = $container->get(LocaleMiddleware::class);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $loader
        );
    }

    public function testEastManagerMiddlewareInjection()
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/universal/di.php');
        $containerDefinition->addDefinitions(__DIR__.'/../../src/universal/di.php');

        $container = $containerDefinition->build();

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(ObjectRepository::class)
        );

        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(ObjectManager::class, $objectManager);

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

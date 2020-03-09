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

namespace Teknoo\Tests\East\Website\Doctrine;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Gedmo\Translatable\TranslatableListener;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

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
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testLegacyDIForward()
    {
        $di1 = include __DIR__.'/../../../src/doctrine/di.php';
        $di2 = include __DIR__.'/../../../infrastructures/doctrine/di.php';

        self::assertEquals(
            $di1,
            $di2
        );
    }

    /**
     * @return Container
     * @throws \Exception
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/doctrine/di.php');

        return $containerDefinition->build();
    }

    public function testManager()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);

        $container->set(ObjectManager::class, $objectManager);
        self::assertInstanceOf(ManagerInterface::class, $container->get(ManagerInterface::class));
    }

    private function generateTestForRepository(string $objectClass, string $repositoryClass)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->with($objectClass)->willReturn(
            $this->createMock(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $repository = $container->get($repositoryClass);

        self::assertInstanceOf(
            $repositoryClass,
            $repository
        );
    }

    private function generateTestForRepositoryWithUnsupportedRepository(string $objectClass, string $repositoryClass)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->with($objectClass)->willReturn(
            $this->createMock(\DateTime::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $container->get($repositoryClass);
    }

    public function testItemRepository()
    {
        $this->generateTestForRepository(Item::class, ItemRepositoryInterface::class);
    }

    public function testContentRepository()
    {
        $this->generateTestForRepository(Content::class, ContentRepositoryInterface::class);
    }

    public function testMediaRepository()
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class);
    }

    public function testTypeRepository()
    {
        $this->generateTestForRepository(Type::class, TypeRepositoryInterface::class);
    }

    public function testUserRepository()
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class);
    }

    public function testItemRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(Item::class, ItemRepositoryInterface::class);
    }

    public function testContentRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(Content::class, ContentRepositoryInterface::class);
    }

    public function testMediaRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(Media::class, MediaRepositoryInterface::class);
    }

    public function testTypeRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(Type::class, TypeRepositoryInterface::class);
    }

    public function testUserRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(User::class, UserRepositoryInterface::class);
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
}

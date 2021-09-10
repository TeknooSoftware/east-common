<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Doctrine;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Doctrine\Object\User;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Service\ProxyDetectorInterface;
use Teknoo\East\Website\Writer\MediaWriter as OriginalWriter;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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

    private function generateTestForRepository(string $objectClass, string $repositoryClass, string $repositoryType)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->with($objectClass)->willReturn(
            $this->createMock($repositoryType)
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

    public function testItemRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(Item::class, ItemRepositoryInterface::class, ObjectRepository::class);
    }

    public function testContentRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(Content::class, ContentRepositoryInterface::class, ObjectRepository::class);
    }

    public function testMediaRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, ObjectRepository::class);
    }

    public function testTypeRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(Type::class, TypeRepositoryInterface::class, ObjectRepository::class);
    }

    public function testUserRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, ObjectRepository::class);
    }

    public function testItemRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(Item::class, ItemRepositoryInterface::class, DocumentRepository::class);
    }

    public function testContentRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(Content::class, ContentRepositoryInterface::class, DocumentRepository::class);
    }

    public function testMediaRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, DocumentRepository::class);
    }

    public function testTypeRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(Type::class, TypeRepositoryInterface::class, DocumentRepository::class);
    }

    public function testUserRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, DocumentRepository::class);
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

    public function testLocaleMiddlewareWithDocumentManager()
    {
        $container = $this->buildContainer();
        $translatableListener = $this->createMock(TranslatableListener::class);

        $objectManager = $this->createMock(DocumentManager::class);
        $container->set(ObjectManager::class, $objectManager);

        $container->set(TranslatableListener::class, $translatableListener);
        $loader = $container->get(LocaleMiddleware::class);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $loader
        );
    }

    public function testLocaleMiddlewareWithoutDocumentManager()
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

    public function testTranslationListenerWithDocumentManagerWithoutMappingDriver()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(DocumentManager::class);
        $container->set(ObjectManager::class, $objectManager);

        $this->expectException(\RuntimeException::class);
        $listener = $container->get(TranslatableListener::class);

        self::assertInstanceOf(
            TranslatableListener::class,
            $listener
        );
    }

    public function testTranslationListenerWithDocumentManager()
    {
        $container = $this->buildContainer();

        $driver = $this->createMock(MappingDriver::class);

        $configuration = $this->createMock(Configuration::class);
        $configuration->expects(self::any())->method('getMetadataDriverImpl')->willReturn($driver);

        $mappingFactory = $this->createMock(AbstractClassMetadataFactory::class);

        $objectManager = $this->createMock(DocumentManager::class);
        $objectManager->expects(self::any())->method('getConfiguration')->willReturn($configuration);
        $objectManager->expects(self::any())->method('getMetadataFactory')->willReturn($mappingFactory);

        $container->set(ObjectManager::class, $objectManager);

        $listener = $container->get(TranslatableListener::class);

        self::assertInstanceOf(
            TranslatableListener::class,
            $listener
        );

        $rf = new \ReflectionObject($listener);
        $rpw = $rf->getProperty('wrapperFactory');

        $rpw->setAccessible(true);
        $closure = $rpw->getValue($listener);

        self::assertInstanceOf(
            WrapperInterface::class,
            $closure(new Content(), $this->createMock(ClassMetadata::class))
        );

        $error = false;
        try {
            $closure(new Content(), $this->createMock(BaseClassMetadata::class));
        } catch (\RuntimeException $error) {
            $error = true;
        }
        self::assertTrue($error);

        $rpe = $rf->getProperty('extensionMetadataFactory');
        $rpe->setAccessible(true);
        $extensionMetadataFactory = $rpe->getValue($listener);

        $rf = new \ReflectionObject($extensionMetadataFactory);

        $rpe = $rf->getProperty('driverFactory');
        $rpe->setAccessible(true);
        $driverFactory = $rpe->getValue($extensionMetadataFactory);

        $driver = $driverFactory($this->createMock(FileLocator::class));
        self::assertInstanceOf(
            DriverInterface::class,
            $driver
        );

        $rf = new \ReflectionObject($driver);

        $rps = $rf->getProperty('simpleXmlFactory');
        $rps->setAccessible(true);
        $simpleXmlFactory = $rps->getValue($driver);

        $simpleXml = $simpleXmlFactory(__DIR__.'/Translatable/Mapping/Driver/support/valid.translate.xml');
        self::assertInstanceOf(
            \SimpleXMLElement::class,
            $simpleXml
        );
    }

    public function testTranslationListenerWithWithoutDocumentManager()
    {
        $container = $this->buildContainer();

        $container->set(ObjectManager::class, $this->createMock(ObjectManager::class));

        $this->expectException(\RuntimeException::class);
        $container->get(TranslatableListener::class);
    }

    public function testProxyDetectorInterface()
    {
        $container = $this->buildContainer();
        $proxyDetector = $container->get(ProxyDetectorInterface::class);

        $p1 = $this->createMock(PromiseInterface::class);
        $p1->expects(self::never())->method('success');
        $p1->expects(self::once())->method('fail');

        self::assertInstanceOf(
            ProxyDetectorInterface::class,
            $proxyDetector->checkIfInstanceBehindProxy(new \stdClass(), $p1)
        );

        $p2 = $this->createMock(PromiseInterface::class);
        $p2->expects(self::never())->method('success');
        $p2->expects(self::once())->method('fail');

        self::assertInstanceOf(
            ProxyDetectorInterface::class,
            $proxyDetector->checkIfInstanceBehindProxy(new class implements GhostObjectInterface {
                public function setProxyInitializer(?\Closure $initializer = null)
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function getProxyInitializer(): ?\Closure
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function initializeProxy(): bool
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function isProxyInitialized(): bool
                {
                    return true;
                }
            }, $p2)
        );

        $p3 = $this->createMock(PromiseInterface::class);
        $p3->expects(self::once())->method('success');
        $p3->expects(self::never())->method('fail');

        self::assertInstanceOf(
            ProxyDetectorInterface::class,
            $proxyDetector->checkIfInstanceBehindProxy(new class implements GhostObjectInterface {
                public function setProxyInitializer(?\Closure $initializer = null)
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function getProxyInitializer(): ?\Closure
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function initializeProxy(): bool
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function isProxyInitialized(): bool
                {
                    return false;
                }
            }, $p3)
        );
    }

    public function testMediaWriterWithValidRepository()
    {
        $this->expectException(\RuntimeException::class);
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(\DateTime::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $container->get(MediaWriter::class);
    }

    public function testMediaWriter()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(GridFSRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $container->set(OriginalWriter::class, $this->createMock(OriginalWriter::class));
        self::assertInstanceOf(
            MediaWriter::class,
            $container->get(MediaWriter::class)
        );
        self::assertInstanceOf(
            MediaWriter::class,
            $container->get('teknoo.east.website.doctrine.writer.media.new')
        );
    }

    public function testGetStreamFromMediaBadRepo()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(DocumentRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);

        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));

        $this->expectException(\RuntimeException::class);
        $container->get(GetStreamFromMedia::class);
    }

    public function testGetStreamFromMedia()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::any())->method('getRepository')->willReturn(
            $this->createMock(GridFSRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);

        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        self::assertInstanceOf(
            GetStreamFromMediaInterface::class,
            $container->get(GetStreamFromMediaInterface::class)
        );
        self::assertInstanceOf(
            GetStreamFromMedia::class,
            $container->get(GetStreamFromMedia::class)
        );
    }
}

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

namespace Teknoo\Tests\East\Common\Doctrine;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Contracts\Service\ProxyDetectorInterface;
use Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Writer\MediaWriter as OriginalWriter;
use Teknoo\Recipe\Promise\PromiseInterface;

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

    public function testBatchManager()
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $batchManager = $this->createMock(BatchManipulationManager::class);

        $container->set(ObjectManager::class, $objectManager);
        $container->set(BatchManipulationManager::class, $batchManager);
        self::assertInstanceOf(BatchManipulationManagerInterface::class, $container->get(BatchManipulationManagerInterface::class));
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

    public function testUserRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, ObjectRepository::class);
    }

    public function testUserRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, DocumentRepository::class);
    }

    public function testUserRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(User::class, UserRepositoryInterface::class);
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
                public function setProxyInitializer(?\Closure $initializer = null): never
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function getProxyInitializer(): never
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function initializeProxy(): never
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
                public function setProxyInitializer(?\Closure $initializer = null): never
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function getProxyInitializer(): never
                {
                    throw new \RuntimeException('Must not be called');
                }

                public function initializeProxy(): never
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

    public function testMediaRepositoryWithObjectRepository()
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, ObjectRepository::class);
    }

    public function testMediaRepositoryWithDocumentRepository()
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, DocumentRepository::class);
    }

    public function testMediaRepositoryWithUnsupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        $this->generateTestForRepositoryWithUnsupportedRepository(Media::class, MediaRepositoryInterface::class);
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
            $container->get('teknoo.east.common.doctrine.writer.media.new')
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

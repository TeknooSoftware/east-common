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

namespace Teknoo\Tests\East\Common\Doctrine;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Writer\MediaWriter as OriginalWriter;

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
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/doctrine/di.php');

        return $containerDefinition->build();
    }

    public function testManager(): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createStub(ObjectManager::class);

        $container->set(ObjectManager::class, $objectManager);
        $this->assertInstanceOf(ManagerInterface::class, $container->get(ManagerInterface::class));
    }

    public function testManagerWithDocumentManager(): void
    {
        $container = $this->buildContainer();
        $objectManager = new class () extends DocumentManager {
            public function __construct()
            {
            }

            public function getConfiguration(): Configuration
            {
                static $configuration;
                return $configuration ??= new Configuration();
            }
        };

        $container->set(ObjectManager::class, $objectManager);
        $this->assertInstanceOf(ManagerInterface::class, $container->get(ManagerInterface::class));
    }

    public function testBatchManager(): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createStub(ObjectManager::class);
        $batchManager = $this->createStub(BatchManipulationManager::class);

        $container->set(ObjectManager::class, $objectManager);
        $container->set(BatchManipulationManager::class, $batchManager);
        $this->assertInstanceOf(BatchManipulationManagerInterface::class, $container->get(BatchManipulationManagerInterface::class));
    }

    private function generateTestForRepository(string $objectClass, string $repositoryClass, string $repositoryType): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->atLeastOnce())->method('getRepository')->with($objectClass)->willReturn(
            $this->createStub($repositoryType)
        );

        $container->set(ObjectManager::class, $objectManager);
        $repository = $container->get($repositoryClass);

        $this->assertInstanceOf(
            $repositoryClass,
            $repository
        );
    }

    public function testUserRepositoryWithObjectRepository(): void
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, ObjectRepository::class);
    }

    public function testUserRepositoryWithDocumentRepository(): void
    {
        $this->generateTestForRepository(User::class, UserRepositoryInterface::class, DocumentRepository::class);
    }

    public function testMediaRepositoryWithObjectRepository(): void
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, ObjectRepository::class);
    }

    public function testMediaRepositoryWithDocumentRepository(): void
    {
        $this->generateTestForRepository(Media::class, MediaRepositoryInterface::class, DocumentRepository::class);
    }

    public function testMediaWriterWithValidRepository(): void
    {
        $this->expectException(\RuntimeException::class);
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->atLeastOnce())->method('getRepository')->willReturn(
            $this->createStub(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $container->get(MediaWriter::class);
    }

    public function testMediaWriter(): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->atLeastOnce())->method('getRepository')->willReturn(
            $this->createStub(GridFSRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);
        $container->set(OriginalWriter::class, $this->createStub(OriginalWriter::class));
        $this->assertInstanceOf(
            MediaWriter::class,
            $container->get(MediaWriter::class)
        );
        $this->assertInstanceOf(
            MediaWriter::class,
            $container->get('teknoo.east.common.doctrine.writer.media.new')
        );
    }

    public function testGetStreamFromMediaBadRepo(): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->atLeastOnce())->method('getRepository')->willReturn(
            $this->createStub(ObjectRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);

        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));

        $this->expectException(\RuntimeException::class);
        $container->get(GetStreamFromMedia::class);
    }

    public function testGetStreamFromMedia(): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->atLeastOnce())->method('getRepository')->willReturn(
            $this->createStub(GridFSRepository::class)
        );

        $container->set(ObjectManager::class, $objectManager);

        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $this->assertInstanceOf(
            GetStreamFromMediaInterface::class,
            $container->get(GetStreamFromMediaInterface::class)
        );
        $this->assertInstanceOf(
            GetStreamFromMedia::class,
            $container->get(GetStreamFromMedia::class)
        );
    }
}

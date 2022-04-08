<?php

/**
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Common\Doctrine;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Service\ProxyDetectorInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
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
}

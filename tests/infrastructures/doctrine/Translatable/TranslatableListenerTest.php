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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface as PersistenceAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\FactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\TranslatableListener
 */
class TranslatableListenerTest extends TestCase
{
    private ExtensionMetadataFactory $extensionMetadataFactory;

    private ManagerAdapterInterface $manager;

    private PersistenceAdapterInterface $persistence;

    private FactoryInterface $wrapperFactory;

    /**
     * @return ExtensionMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getExtensionMetadataFactory(): ExtensionMetadataFactory
    {
        if (!$this->extensionMetadataFactory instanceof ExtensionMetadataFactory) {
            $this->extensionMetadataFactory = $this->createMock(ExtensionMetadataFactory::class);
        }

        return $this->extensionMetadataFactory;
    }

    /**
     * @return ManagerAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getManager(): ManagerAdapterInterface
    {
        if (!$this->manager instanceof ManagerAdapterInterface) {
            $this->manager = $this->createMock(ManagerAdapterInterface::class);
        }

        return $this->manager;
    }

    /**
     * @return PersistenceAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getPersistence(): PersistenceAdapterInterface
    {
        if (!$this->persistence instanceof PersistenceAdapterInterface) {
            $this->persistence = $this->createMock(PersistenceAdapterInterface::class);
        }

        return $this->persistence;
    }

    /**
     * @return FactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWrapperFactory(): FactoryInterface
    {
        if (!$this->wrapperFactory instanceof FactoryInterface) {
            $this->wrapperFactory = $this->createMock(FactoryInterface::class);
        }

        return $this->wrapperFactory;
    }

    public function build(string $locale = 'en', bool $fallback = true): TranslatableListener
    {
        return new TranslatableListener(
            $this->getExtensionMetadataFactory(),
            $this->getManager(),
            $this->getPersistence(),
            $this->getWrapperFactory(),
            $locale,
            'en',
            $fallback
        );
    }

    public function testGetSubscribedEvents()
    {
    }

    public function testRegisterClassMetadata()
    {
    }

    public function testSetLocale()
    {
    }

    public function testInjectConfiguration(ClassMetadata $metadata, array $config)
    {
    }

    public function testLoadClassMetadata()
    {
    }

    public function testPostLoad()
    {
    }

    public function testOnFlush()
    {
    }

    public function testPostFlush()
    {
    }

    public function testPostPersist()
    {
    }
}

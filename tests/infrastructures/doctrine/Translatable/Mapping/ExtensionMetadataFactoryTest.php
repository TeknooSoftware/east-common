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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\Mapping;

use Doctrine\Common\Cache\Cache;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;
use Teknoo\East\Website\Doctrine\Object\Content as DoctrineContent;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverFactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory
 */
class ExtensionMetadataFactoryTest extends TestCase
{
    private ?ObjectManager $objectManager = null;

    private ?AbstractClassMetadataFactory $classMetadataFactory = null;

    private ?MappingDriver $mappingDriver = null;

    private ?DriverFactoryInterface $driverFactory = null;

    /**
     * @return ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getObjectManager(): ObjectManager
    {
        if (!$this->objectManager instanceof ObjectManager) {
            $this->objectManager = $this->createMock(ObjectManager::class);
        }

        return $this->objectManager;
    }

    /**
     * @return AbstractClassMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getClassMetadataFactory(): AbstractClassMetadataFactory
    {
        if (!$this->classMetadataFactory instanceof AbstractClassMetadataFactory) {
            $this->classMetadataFactory = $this->createMock(AbstractClassMetadataFactory::class);
        }

        return $this->classMetadataFactory;
    }

    /**
     * @return MappingDriver|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMappingDriver(): MappingDriver
    {
        if (!$this->mappingDriver instanceof MappingDriver) {
            $this->mappingDriver = $this->createMock(MappingDriver::class);
        }

        return $this->mappingDriver;
    }

    /**
     * @return DriverFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDriverFactory(): DriverFactoryInterface
    {
        if (!$this->driverFactory instanceof DriverFactoryInterface) {
            $this->driverFactory = $this->createMock(DriverFactoryInterface::class);

            $this->driverFactory->expects(self::any())
                ->method('__invoke')
                ->willReturnCallback(function () {
                    $driver = $this->createMock(DriverInterface::class);
                    $driver->expects(self::any())
                        ->method('readExtendedMetadata')
                        ->willReturnCallback(
                            function (ClassMetadata $meta, array &$config) use ($driver) {
                                $config['fields'] = ['foo', 'bar'];
                                $config['fallbacks'] = ['foo', 'bar'];

                                return $driver;
                            }
                        );

                    return $driver;
                });
        }

        return $this->driverFactory;
    }

    public function build():ExtensionMetadataFactory
    {
        return new ExtensionMetadataFactory(
            $this->getObjectManager(),
            $this->getClassMetadataFactory(),
            $this->getMappingDriver(),
            $this->getDriverFactory()
        );
    }

    public function testLoadExtensionMetadataSuperClass()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = true;

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::never())->method('injectConfiguration');

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataMissingDriver()
    {
        $this->expectException(InvalidMappingException::class);

        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(Content::class);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::never())->method('injectConfiguration');

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataWithFileDriver()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(Content::class);

        $this->mappingDriver = $this->createMock(FileDriver::class);

        $locator = $this->createMock(FileLocator::class);
        $this->mappingDriver->expects(self::any())->method('getLocator')->willReturn($locator);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('injectConfiguration');

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataWithMappingDriverChain()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(Content::class);

        $this->mappingDriver = $this->createMock(MappingDriverChain::class);
        $fileDriver = $this->createMock(FileDriver::class);

        $this->mappingDriver->expects(self::any())->method('getDrivers')->willReturn([
            $this->createMock(MappingDriver::class),
            $this->createMock(MappingDriver::class),
            $fileDriver
        ]);

        $locator = $this->createMock(FileLocator::class);
        $fileDriver->expects(self::any())->method('getLocator')->willReturn($locator);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('injectConfiguration');

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataWithFileDriverWithParent()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(DoctrineContent::class);

        $this->mappingDriver = $this->createMock(FileDriver::class);

        $locator = $this->createMock(FileLocator::class);
        $this->mappingDriver->expects(self::any())->method('getLocator')->willReturn($locator);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('injectConfiguration');

        $this->getClassMetadataFactory()
            ->expects(self::any())
            ->method('hasMetadataFor')
            ->willReturn(true);

        $this->getObjectManager()
            ->expects(self::any())
            ->method('getClassMetadata')
            ->willReturn($this->createMock(ClassMetadata::class));

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataWitchCacheEmpty()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(DoctrineContent::class);

        $this->mappingDriver = $this->createMock(FileDriver::class);

        $locator = $this->createMock(FileLocator::class);
        $this->mappingDriver->expects(self::any())->method('getLocator')->willReturn($locator);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('injectConfiguration');

        $cache = $this->createMock(Cache::class);
        $cache->expects(self::any())->method('contains')->willReturn(false);
        $this->getClassMetadataFactory()->expects(self::any())->method('getCacheDriver')->willReturn($cache);

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }

    public function testLoadExtensionMetadataWitchCacheNotEmpty()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $meta->isMappedSuperclass = false;
        $meta->expects(self::any())->method('getName')->willReturn(DoctrineContent::class);

        $this->mappingDriver = $this->createMock(FileDriver::class);

        $locator = $this->createMock(FileLocator::class);
        $this->mappingDriver->expects(self::never())->method('getLocator')->willReturn($locator);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('injectConfiguration');

        $cache = $this->createMock(Cache::class);
        $cache->expects(self::any())->method('contains')->willReturn(true);
        $cache->expects(self::any())->method('fetch')->willReturn([]);
        $this->getClassMetadataFactory()->expects(self::any())->method('getCacheDriver')->willReturn($cache);

        self::assertInstanceOf(
            ExtensionMetadataFactory::class,
            $this->build()->loadExtensionMetadata($meta, $listener)
        );
    }
}

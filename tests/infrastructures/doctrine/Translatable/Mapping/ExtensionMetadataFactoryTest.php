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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverFactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
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
    private ObjectManager $objectManager;

    private AbstractClassMetadataFactory $classMetadataFactory;

    private MappingDriver $mappingDriver;

    private DriverFactoryInterface $driverFactory;

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

    }

    public function testLoadExtensionMetadata()
    {

    }

    public function testLoadExtensionMetadataWitchCache()
    {

    }
}

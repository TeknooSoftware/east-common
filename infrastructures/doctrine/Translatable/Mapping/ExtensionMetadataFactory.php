<?php

/*
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
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Mapping;

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;
use Teknoo\East\Website\Doctrine\Exception\RuntimeException;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;

/*
 * The extension metadata factory is responsible for extension driver
 * initialization and fully reading the extension metadata
 */
/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class ExtensionMetadataFactory
{
    private ObjectManager $objectManager;

    private AbstractClassMetadataFactory $classMetadataFactory;

    private MappingDriver $mappingDriver;

    private DriverFactoryInterface $driverFactory;

    public function __construct(
        ObjectManager $objectManager,
        AbstractClassMetadataFactory $classMetadataFactory,
        MappingDriver $mappingDriver,
        DriverFactoryInterface $driverFactory
    ) {
        $this->objectManager = $objectManager;
        $this->classMetadataFactory = $classMetadataFactory;
        $this->mappingDriver = $mappingDriver;
        $this->driverFactory = $driverFactory;
    }

    private function getDriver(): DriverInterface
    {
        $omDriver = $this->mappingDriver;
        if ($omDriver instanceof MappingDriverChain) {
            $drivers = $omDriver->getDrivers();
            foreach ($drivers as $namespace => $nestedOmDriver) {
                if ($nestedOmDriver instanceof FileDriver) {
                    $omDriver = $nestedOmDriver;

                    break;
                }
            }
        }

        if (!$omDriver instanceof FileDriver) {
            throw new InvalidMappingException('Driver not found');
        }

        return ($this->driverFactory)($omDriver->getLocator());
    }

    private static function getCacheId(string $className): string
    {
        return $className . '\\$_TRANSLATE_METADATA';
    }

    public function loadExtensionMetadata(
        ClassMetadata $metaData,
        TranslatableListener $listener
    ): self {
        if (!empty($metaData->isMappedSuperclass)) {
            return $this;
        }

        $config = [];

        $cacheId = self::getCacheId($metaData->getName());
        $cacheDriver = $this->classMetadataFactory->getCacheDriver();

        if (null !== $cacheDriver && $cacheDriver->contains($cacheId)) {
            $listener->injectConfiguration($metaData, $cacheDriver->fetch($cacheId));

            return $this;
        }

        $driver = $this->getDriver();
        $useObjectName = $metaData->getName();

        // collect metadata from inherited classes
        foreach (\array_reverse(\class_parents($useObjectName)) as $parentClass) {
            // read only inherited mapped classes
            if ($this->classMetadataFactory->hasMetadataFor($parentClass)) {
                $parentMetaClass = $this->objectManager->getClassMetadata($parentClass);
                $driver->readExtendedMetadata($parentMetaClass, $config);

                if (
                    empty($parentMetaClass->parentClasses)
                    && !empty($config)
                    && (
                        !\is_callable([$parentMetaClass, 'isInheritanceTypeNone'])
                        || !$parentMetaClass->isInheritanceTypeNone()
                    )
                ) {
                    $useObjectName = $parentMetaClass->getName();
                }
            }
        }

        $driver->readExtendedMetadata($metaData, $config);

        if (!empty($config)) {
            $config['useObjectClass'] = $useObjectName;
        }

        if (null !== $cacheDriver) {
            $cacheDriver->save($cacheId, $config);
        }
        
        $listener->injectConfiguration($metaData, $config);

        return $this;
    }
}

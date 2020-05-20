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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\Xml as XmlDriver;

/**
 * The extension metadata factory is responsible for extension driver
 * initialization and fully reading the extension metadata
 */
class ExtensionMetadataFactory
{
    private XmlDriver $driver;

    private ObjectManager $objectManager;

    /**
     * @param ObjectManager|DocumentManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        $mappingDriver = $objectManager->getConfiguration()->getMetadataDriverImpl();

        //todo
        if ($mappingDriver instanceof MappingDriver) {
            throw new \RuntimeException('error');
        }

        $this->driver = $this->getDriver($mappingDriver);
    }

    private function getDriver(MappingDriver $omDriver): XmlDriver
    {
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

        //todo
        return new XmlDriver($omDriver->getLocator(), $omDriver);
    }

    public function getExtensionMetadata(ClassMetadata $meta): array
    {
        if (!empty($meta->isMappedSuperclass)) {
            return []; // ignore mappedSuperclasses for now
        }

        $config = [];
        $cmf = $this->objectManager->getMetadataFactory();

        $useObjectName = $meta->getName();

        // collect metadata from inherited classes
        foreach (\array_reverse(\class_parents($useObjectName)) as $parentClass) {
            // read only inherited mapped classes
            if ($cmf->hasMetadataFor($parentClass)) {
                $parentMetaClass = $this->objectManager->getClassMetadata($parentClass);
                $this->driver->readExtendedMetadata($parentMetaClass, $config);

                if (
                    empty($parentMetaClass->parentClasses)
                    && !empty($config)
                    && !$parentMetaClass->isInheritanceTypeNone()
                ) {
                    $useObjectName = $parentMetaClass->getName();
                }
            }
        }

        $this->driver->readExtendedMetadata($meta, $config);

        if (!empty($config)) {
            $config['useObjectClass'] = $useObjectName;
        }

        // cache the metadata (even if it's empty)
        // caching empty metadata will prevent re-parsing non-existent annotations
        $cacheId = self::getCacheId($meta->name);
        if ($cacheDriver = $cmf->getCacheDriver()) {
            $cacheDriver->save($cacheId, $config, null);
        }

        return $config;
    }

    private static function getCacheId(string $className): string
    {
        return $className.'\\$_CLASSMETADATA';
    }
}

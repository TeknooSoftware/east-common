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

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\Xml as XmlDriver;

/**
 * The extension metadata factory is responsible for extension driver
 * initialization and fully reading the extension metadata
 */
class ExtensionMetadataFactory
{
    private function getDriver(ObjectManager $objectManager): XmlDriver
    {
        $omDriver = $objectManager->getConfiguration()->getMetadataDriverImpl();
        if ($omDriver instanceof MappingDriver) {
            throw new \RuntimeException('error');
        }

        $drivers = $omDriver->getDrivers();
        foreach ($drivers as $namespace => $nestedOmDriver) {
            if ($nestedOmDriver instanceof FileDriver) {
                $omDriver = $nestedOmDriver;

                break;
            }
        }

        if (!$omDriver instanceof FileDriver) {
            throw new InvalidMappingException('Driver not found');
        }

        //todo
        return new XmlDriver($omDriver->getLocator(), $omDriver);
    }

    public function getExtensionMetadata(ObjectManager $objectManager, ClassMetadata $meta): array
    {
        if (!empty($meta->isMappedSuperclass)) {
            return []; // ignore mappedSuperclasses for now
        }

        $config = [];
        $cmf = $objectManager->getMetadataFactory();

        $cacheId = self::getCacheId($meta->getName());
        $cacheDriver = $cmf->getCacheDriver();

        if (null !== $cacheDriver && $cacheDriver->contains($cacheId)) {
            return $cacheDriver->fetch($cacheId);
        }

        $driver = $this->getDriver($objectManager);
        $useObjectName = $meta->getName();

        // collect metadata from inherited classes
        foreach (\array_reverse(\class_parents($useObjectName)) as $parentClass) {
            // read only inherited mapped classes
            if ($cmf->hasMetadataFor($parentClass)) {
                $parentMetaClass = $objectManager->getClassMetadata($parentClass);
                $driver->readExtendedMetadata($parentMetaClass, $config);

                if (
                    empty($parentMetaClass->parentClasses)
                    && !empty($config)
                    && !$parentMetaClass->isInheritanceTypeNone()
                ) {
                    $useObjectName = $parentMetaClass->getName();
                }
            }
        }

        $driver->readExtendedMetadata($meta, $config);

        if (!empty($config)) {
            $config['useObjectClass'] = $useObjectName;
        }

        if (null !== $cacheDriver) {
            $cacheDriver->save($cacheId, $config, null);
        }

        return $config;
    }

    private static function getCacheId(string $className): string
    {
        return $className.'\\$_CLASSMETADATA';
    }
}

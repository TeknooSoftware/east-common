<?php

namespace Teknoo\East\Website\Gedmo;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ODM\MongoDB\Mapping\Driver as DriverMongodbODM;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;

/**
 * Version class allows to checking the dependencies required
 * and the current version of doctrine extensions
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
final class DoctrineExtensions
{
    /**
     * Current version of extensions
     */
    const VERSION = 'v2.4.26';

    /**
     * Hooks all extensions metadata mapping drivers
     * into given $driverChain of drivers for ODM MongoDB
     *
     * @param MappingDriverChain $driverChain
     * @param Reader|null        $reader
     */
    public static function registerMappingIntoDriverChainMongodbODM(MappingDriverChain $driverChain, Reader $reader = null)
    {
        self::registerAnnotations();
        if (!$reader) {
            $reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        }
        $annotationDriver = new DriverMongodbODM\AnnotationDriver($reader, array(
            __DIR__.'/Translatable/Document',
            __DIR__.'/Loggable/Document',
        ));
        $driverChain->addDriver($annotationDriver, 'Teknoo\East\Website\Gedmo');
    }

    /**
     * Hooks only superclass metadata mapping drivers
     * into given $driverChain of drivers for ODM MongoDB
     *
     * @param MappingDriverChain $driverChain
     * @param Reader|null        $reader
     */
    public static function registerAbstractMappingIntoDriverChainMongodbODM(MappingDriverChain $driverChain, Reader $reader = null)
    {
        self::registerAnnotations();
        if (!$reader) {
            $reader = new CachedReader(new AnnotationReader(), new ArrayCache());
        }
        $annotationDriver = new DriverMongodbODM\AnnotationDriver($reader, array(
            __DIR__.'/Translatable/Document/MappedSuperclass',
            __DIR__.'/Loggable/Document/MappedSuperclass',
        ));
        $driverChain->addDriver($annotationDriver, 'Teknoo\East\Website\Gedmo');
    }

    /**
     * Includes all extension annotations once
     */
    public static function registerAnnotations()
    {
        AnnotationRegistry::registerFile(__DIR__.'/Mapping/Annotation/All.php');
    }
}

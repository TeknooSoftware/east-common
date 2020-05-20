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
 * @author      Richard Déloge <richarddeloge@gmail.com
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author      Miha Vrhovnik <miha.vrhovnik@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver;

use Doctrine\Persistence\Mapping\Driver\FileDriver;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Teknoo\East\Website\Doctrine\Object\Translation;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;

class Xml implements Driver
{
    const DOCTRINE_NAMESPACE_URI = 'http://xml.teknoo.it/schemas/odm/east-website-mapping';

    private FileLocator $locator;

    private FileDriver $originalDriver;

    public function __construct(FileLocator $locator, FileDriver $originalDriver)
    {
        $this->locator = $locator;
        $this->originalDriver = $originalDriver;
    }

    private function getMapping(string $className): \SimpleXMLElement
    {
        //todo heuuu
        $mapping = null;
        if ($this->originalDriver instanceof FileDriver) {
            return $this->originalDriver->getElement($className);
        }

        $xml = $this->loadMappingFile($this->locator->findMappingFile($className));

        return $xml[$className];
    }

    private function loadMappingFile($file): array
    {
        $result = [];
        $xmlElement = new \SimpleXMLElement($file);
        $xmlElement = $xmlElement->children(self::DOCTRINE_NAMESPACE_URI);

        $extractFunction = function ($nodeName) use ($xmlElement, &$result) {
            if (!isset($xmlElement->$nodeName)) {
                return;
            }

            foreach ($xmlElement->{$nodeName} as $element) {
                $className = $element->attributes()['name'];
                $result[$className] = $element;
            }
        };

        $extractFunction('entity');
        $extractFunction('document');
        $extractFunction('mapped-superclass');

        return $result;
    }

    private function inspectElementsForTranslatableFields(
        \SimpleXMLElement $xml,
        array &$config,
        ?string $prefix = null
    ): void {
        if (!isset($xml->field)) {
            return;
        }

        foreach ($xml->field as $mapping) {
            $fieldName = $mapping->attributes()['name'];
            if (null !== $prefix) {
                $fieldName = $prefix . '.' . $fieldName;
            }

            $this->buildFieldConfiguration($mapping, $fieldName, $config);
        }
    }

    private function buildFieldConfiguration(\SimpleXMLElement $mapping, string $fieldName, array &$config): void
    {
        $mapping = $mapping->children();
        if ($mapping->count() > 0 && isset($mapping->translatable)) {
            $config['fields'][] = $fieldName;

            $config['fallback'][$fieldName] = $mapping->translatable->attributes()['fallback'] ?? true;
        }
    }

    public function readExtendedMetadata(ClassMetadata $meta, array &$config): void
    {
        $xml = $this->getMapping($meta->name);

        if (\in_array($xml->getName(), 'entity', 'document', 'mapped-superclass')) {
            if (isset($xml->translation)) {
                $translationAttr = $xml->translation->attributes();

                $config['locale'] = $translationAttr['locale'] ?? 'localeField';
                $config['translationClass'] = $translationAttr['class'] ?? Translation::class;

                if (!\class_exists($config['translationClass'])) {
                    throw new InvalidMappingException(
                        "Translation entity class: {$config['translationClass']} does not exist."
                    );
                }
            }
        }

        $this->inspectElementsForTranslatableFields($xml, $config);

        if (!$meta->isMappedSuperclass && !empty($config)) {
            if (\is_array($meta->identifier) && 1 < \count($meta->identifier)) {
                throw new InvalidMappingException(
                    "TranslatableInterface does not support composite identifiers in class - {$meta->name}"
                );
            }
        }
    }
}

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
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverInterface;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;

class Xml implements DriverInterface
{
    const DOCTRINE_NAMESPACE_URI = 'http://xml.teknoo.it/schemas/doctrine/east-website-translation';

    private FileLocator $locator;

    private SimpleXmlFactoryInterface $simpleXmlFactory;

    public function __construct(
        FileLocator $locator,
        SimpleXmlFactoryInterface $simpleXmlFactory
    ) {
        $this->locator = $locator;
        $this->simpleXmlFactory = $simpleXmlFactory;
    }

    private function getMapping(string $className): ?\SimpleXMLElement
    {
        $file = $this->locator->findMappingFile($className);
        $file = \str_replace('.xml', '.translate.xml', $file);

        if (!\file_exists($file)) {
            return null;
        }

        return $this->loadMappingFile($file);
    }

    private function loadMappingFile($file): \SimpleXMLElement
    {
        $result = null;
        $xmlElement = ($this->simpleXmlFactory)($file);
        $xmlElement = $xmlElement->children(self::DOCTRINE_NAMESPACE_URI);

        if (!isset($xmlElement->object)) {
            throw new \RuntimeException('error');
        }

        return $xmlElement->object;
    }

    private function inspectElementsForTranslatableFields(
        \SimpleXMLElement $xml,
        array &$config
    ): void {
        if (!isset($xml->field)) {
            return;
        }

        foreach ($xml->field as $mapping) {
            $attributes = $mapping->attributes();
            $fieldName = (string) $attributes['field-name'];

            $config['fields'][] = $fieldName;
            $config['fallback'][$fieldName] = (bool) ($attributes['fallback'] ?? true);
        }
    }

    public function readExtendedMetadata(ClassMetadata $meta, array &$config): self
    {
        $xml = $this->getMapping($meta->getName());

        if (null === $xml) {
            return $this;
        }

        $config['translationClass'] = (string) ($xml->attributes()['translation-class'] ?? Translation::class);

        if (!\class_exists($config['translationClass'])) {
            throw new InvalidMappingException(
                "Translation entity class: {$config['translationClass']} does not exist."
            );
        }

        $this->inspectElementsForTranslatableFields($xml, $config);

        return $this;
    }
}

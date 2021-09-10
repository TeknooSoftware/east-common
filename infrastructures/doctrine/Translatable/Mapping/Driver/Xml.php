<?php

/*
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
 * @author      Richard Déloge <richarddeloge@gmail.com
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author      Miha Vrhovnik <miha.vrhovnik@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver;

use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use RuntimeException;
use SimpleXMLElement;
use Teknoo\East\Website\Doctrine\Object\Translation;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverInterface;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;

use function class_exists;
use function file_exists;
use function str_replace;

/**
 * Driver implementation to read Doctrine Translation configuration/metadata in XML files
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author      Miha Vrhovnik <miha.vrhovnik@gmail.com>
 */
class Xml implements DriverInterface
{
    private const DOCTRINE_NAMESPACE_URI = 'http://xml.teknoo.it/schemas/doctrine/east-website-translation';

    public function __construct(
        private FileLocator $locator,
        private SimpleXmlFactoryInterface $simpleXmlFactory,
    ) {
    }

    private function getMapping(string $className): ?SimpleXMLElement
    {
        $file = (string) $this->locator->findMappingFile($className);
        $file = str_replace('.xml', '.translate.xml', $file);

        if (!file_exists($file)) {
            return null;
        }

        return $this->loadMappingFile($file);
    }

    private function loadMappingFile(string $file): SimpleXMLElement
    {
        $xmlElement = ($this->simpleXmlFactory)($file);
        $xmlElement = $xmlElement->children(self::DOCTRINE_NAMESPACE_URI);

        if (!isset($xmlElement->object)) {
            throw new RuntimeException('error');
        }

        return $xmlElement->object;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function inspectElementsForTranslatableFields(
        SimpleXMLElement $xml,
        array &$config
    ): void {
        $config['fields'] = [];
        $config['fallback'] = [];

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

    /**
     * @param array<string, mixed> $config
     */
    public function readExtendedMetadata(ClassMetadata $meta, array &$config): self
    {
        $xml = $this->getMapping($meta->getName());

        if (null === $xml) {
            return $this;
        }

        $config['translationClass'] = (string) ($xml->attributes()['translation-class'] ?? Translation::class);

        if (!class_exists($config['translationClass'])) {
            throw new InvalidMappingException(
                "Translation entity class: {$config['translationClass']} does not exist."
            );
        }

        $this->inspectElementsForTranslatableFields($xml, $config);

        return $this;
    }
}

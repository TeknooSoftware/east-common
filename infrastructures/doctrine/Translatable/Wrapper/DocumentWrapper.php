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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Wrapper;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use ProxyManager\Proxy\GhostObjectInterface;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DocumentWrapper implements WrapperInterface
{
    private ?string $identifier = null;

    private TranslatableInterface $object;

    private ClassMetadata $meta;

    public function __construct(TranslatableInterface $object, ClassMetadata $meta)
    {
        $this->object = $object;
        $this->meta = $meta;
    }

    private function initialize(): void
    {
        if ($this->object instanceof GhostObjectInterface && !$this->object->isProxyInitialized()) {
            $this->object->initializeProxy();
        }
    }

    private function getObject(): TranslatableInterface
    {
        return $this->object;
    }

    /**
     * @return mixed
     */
    private function getPropertyValue(string $name)
    {
        $this->initialize();

        $propertyReflection = $this->meta->getReflectionProperty($name);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($this->object);
    }

    public function setOriginalObjectProperty(ManagerAdapterInterface $manager, string $name): WrapperInterface
    {
        // ensure clean changeset
        $manager->setOriginalObjectProperty(
            \spl_object_hash($this->getObject()),
            $name,
            $this->getPropertyValue($name)
        );

        return $this;
    }

    /**
     * @param Type $type
     */
    public function updateTranslationRecord(
        TranslationInterface $translation,
        string $name,
        $type
    ): WrapperInterface {
        $value = $this->getPropertyValue($name);

        $translation->setContent((string) $type->convertToDatabaseValue($value));

        return $this;
    }

    public function setPropertyValue(string $name, $value): WrapperInterface
    {
        $this->initialize();

        $propertyReflection = $this->meta->getReflectionProperty($name);
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($this->object, $value);

        return $this;
    }

    private function getIdentifier(): string
    {
        return $this->object->getId();
    }

    public function linkTranslationRecord(TranslationInterface $translation): WrapperInterface
    {
        $translation->setForeignKey($this->getIdentifier());

        return $this;
    }

    public function loadTranslations(
        AdapterInterface $adapter,
        string $locale,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): WrapperInterface {
        $adapter->loadTranslations(
            $locale,
            $this->getIdentifier(),
            $translationClass,
            $objectClass,
            $callback
        );

        return $this;
    }

    public function findTranslation(
        AdapterInterface $adapter,
        string $locale,
        string $field,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): WrapperInterface {
        $adapter->findTranslation(
            $locale,
            $field,
            $this->getIdentifier(),
            $translationClass,
            $objectClass,
            $callback
        );

        return $this;
    }

    public function removeAssociatedTranslations(
        AdapterInterface $adapter,
        string $translationClass,
        string $objectClass
    ): WrapperInterface {
        $adapter->removeAssociatedTranslations(
            $this->getIdentifier(),
            $translationClass,
            $objectClass
        );

        return $this;
    }
}

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
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Persistence\Adapter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as OdmClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;

class ODM implements AdapterInterface
{
    private DocumentManager $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function loadTranslations(
        string $locale,
        string $identifier,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): AdapterInterface {
        // load translated content for all translatable fields construct query
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->field('foreignKey')->equals($identifier);
        $queryBuilder->field('locale')->equals($locale);
        $queryBuilder->field('objectClass')->equals($objectClass);

        $query = $queryBuilder->getQuery();
        $query->setHydrate(false);

        $result = $query->execute();

        $callback($result);

        return $this;
    }

    public function findTranslation(
        string $locale,
        string $field,
        string $identifier,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): AdapterInterface {
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->field('locale')->equals($locale);
        $queryBuilder->field('field')->equals($field);
        $queryBuilder->field('foreignKey')->equals($identifier);
        $queryBuilder->field('objectClass')->equals($objectClass);

        $queryBuilder->limit(1);

        $query = $queryBuilder->getQuery();
        $result = $query->getSingleResult();

        if (!$result instanceof TranslationInterface) {
            $callback($result);
        }

        return $this;
    }

    public function removeAssociatedTranslations(
        string $identifier,
        string $translationClass,
        string $objectClass
    ): AdapterInterface {
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->remove();
        $queryBuilder->field('foreignKey')->equals($identifier);
        $queryBuilder->field('objectClass')->equals($objectClass);

        $query = $queryBuilder->getQuery();
        $query->execute();

        return $this;
    }

    private function prepareId(OdmClassMetadata $metadata, TranslationInterface $translation)
    {
        if (
            null === $metadata->idGenerator
            || OdmClassMetadata::GENERATOR_TYPE_NONE === $metadata->generatorType
            || !empty($translation->getIdentifier())
        ) {
            return;
        }

        $idValue = $metadata->idGenerator->generate($this->manager, $translation);
        $idValue = $metadata->getPHPIdentifierValue($metadata->getDatabaseIdentifierValue($idValue));
        $metadata->setIdentifierValue($translation, $idValue);
    }

    private function generateInsertionArray(OdmClassMetadata $metadata, TranslationInterface $translation): array
    {
        $final = [];
        foreach ($metadata->getFieldNames() as $fieldName) {
            $fm = $metadata->getFieldMapping($fieldName);
            $final[$fm['name'] ?? $fm['fieldName']] = $metadata->getFieldValue($translation, $fieldName);
        }

        return $final;
    }

    public function insertTranslationRecord(TranslationInterface $translation): AdapterInterface
    {
        $meta = $this->manager->getClassMetadata(\get_class($translation));

        $className = $meta->getName();
        $collection = $this->manager->getDocumentCollection($className);
        if (empty($translation->getIdentifier())) {
            $this->prepareId($meta, $translation);
            $collection->insertOne($this->generateInsertionArray($meta, $translation));
        } else {
            $collection->updateOne(
                ['_id' => $translation->getIdentifier()],
                ['$set' => $this->generateInsertionArray($meta, $translation)]
            );
        }

        return $this;
    }

    private function getType(string $type): Type
    {
        return Type::getType($type);
    }

    public function updateTranslationRecord(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        TranslationInterface $translation
    ): AdapterInterface {
        $mapping = $metadata->getFieldMapping($field);

        $type = $this->getType($mapping['type']);

        $wrapped->updateTranslationRecord($translation, $field, $type);

        return $this;
    }

    public function setTranslationValue(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        $value
    ): AdapterInterface {
        $mapping = $metadata->getFieldMapping($field);
        $type = $this->getType($mapping['type']);

        $value = $type->convertToPHPValue($value);
        $wrapped->setPropertyValue($field, $value);

        return $this;
    }
}

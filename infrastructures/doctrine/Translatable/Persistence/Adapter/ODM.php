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
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Persistence\Adapter;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as OdmClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\ClassMetadata;
use MongoDB\BSON\ObjectId;
use RuntimeException;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;

use function strlen;

/**
 * Doctrine ODM adapter able to load and write translated value into a `TranslationInterface` document implementation
 * for each field of a translatable object, and load, in an optimized query, all translations for an object
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ODM implements AdapterInterface
{
    public function __construct(
        private DocumentManager $manager,
    ) {
    }

    public function loadAllTranslations(
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

        if ($result instanceof TranslationInterface) {
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

    private function prepareId(OdmClassMetadata $metadata, TranslationInterface $translation): void
    {
        if (
            OdmClassMetadata::GENERATOR_TYPE_NONE === $metadata->generatorType
            || !empty($translation->getIdentifier())
        ) {
            return;
        }

        if (null === $metadata->idGenerator) {
            throw new RuntimeException('Missing Id Generator');
        }

        $idValue = $metadata->idGenerator->generate($this->manager, $translation);
        $idValue = $metadata->getPHPIdentifierValue($metadata->getDatabaseIdentifierValue($idValue));
        $metadata->setIdentifierValue($translation, $idValue);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function generateInsertionArray(
        OdmClassMetadata $metadata,
        TranslationInterface $translation,
        mixed $id
    ): array {
        $final = [];
        foreach ($metadata->getFieldNames() as $fieldName) {
            $fm = $metadata->getFieldMapping($fieldName);

            if (null !== $id && !empty($fm['id'])) {
                $final[$fm['name'] ?? $fm['fieldName']] = $id;

                continue;
            }

            $final[$fm['name'] ?? $fm['fieldName']] = $metadata->getFieldValue($translation, $fieldName);
        }

        return $final;
    }

    public function persistTranslationRecord(TranslationInterface $translation): AdapterInterface
    {
        $meta = $this->manager->getClassMetadata($translation::class);

        $className = $meta->getName();
        $collection = $this->manager->getDocumentCollection($className);
        if (empty($translation->getIdentifier())) {
            $this->prepareId($meta, $translation);
            $collection->insertOne($this->generateInsertionArray($meta, $translation, null));
        } else {
            $id = $translation->getIdentifier();

            if (24 === strlen($id)) {
                $id = new ObjectId($id);
            }

            $set = $this->generateInsertionArray($meta, $translation, $id);

            $collection->updateOne(
                ['_id' => $id],
                ['$set' => $set]
            );
        }

        return $this;
    }

    private function getType(string $type): Type
    {
        return Type::getType($type);
    }

    /**
     * @param ClassMetadata<OdmClassMetadata> $metadata
     */
    public function updateTranslationRecord(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        TranslationInterface $translation
    ): AdapterInterface {
        if (!$metadata instanceof OdmClassMetadata) {
            throw new RuntimeException('Error this classMetadata is not compatible with this adapter');
        }

        $mapping = $metadata->getFieldMapping($field);

        $type = $this->getType($mapping['type']);

        $wrapped->updateTranslationRecord($translation, $field, $type);

        return $this;
    }

    /**
     * @param ClassMetadata<OdmClassMetadata> $metadata
     */
    public function setTranslatedValue(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        mixed $value
    ): AdapterInterface {
        if (!$metadata instanceof OdmClassMetadata) {
            throw new RuntimeException('Error this classMetadata is not compatible with this adapter');
        }

        $mapping = $metadata->getFieldMapping($field);
        $type = $this->getType($mapping['type']);

        $value = $type->convertToPHPValue($value);
        $wrapped->setPropertyValue($field, $value);

        return $this;
    }
}

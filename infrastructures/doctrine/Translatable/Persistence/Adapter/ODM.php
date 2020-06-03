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
        WrapperInterface $wrapped,
        string $locale,
        string $translationClass,
        string $objectClass
    ): iterable {
        // load translated content for all translatable fields
        // construct query
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->field('foreignKey')->equals($wrapped->getIdentifier());
        $queryBuilder->field('locale')->equals($locale);
        $queryBuilder->field('objectClass')->equals($objectClass);

        $query = $queryBuilder->getQuery();
        $query->setHydrate(false);

        $result = $query->execute();

        if (empty($result)) {
            return [];
        }

        return $result;
    }

    public function findTranslation(
        WrapperInterface $wrapped,
        string $locale,
        string $field,
        string $translationClass,
        string $objectClass
    ): ?TranslationInterface {
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->field('locale')->equals($locale);
        $queryBuilder->field('field')->equals($field);
        $queryBuilder->field('foreignKey')->equals($wrapped->getIdentifier());
        $queryBuilder->field('objectClass')->equals($objectClass);
        $queryBuilder->limit(1);

        $query = $queryBuilder->getQuery();
        $result = $query->getSingleResult();

        if (!$result instanceof TranslationInterface) {
            return null;
        }

        return $result;
    }

    public function removeAssociatedTranslations(
        WrapperInterface $wrapped,
        string $translationClass,
        string $objectClass
    ): void {
        $queryBuilder = $this->manager->createQueryBuilder($translationClass);
        $queryBuilder->remove();
        $queryBuilder->field('foreignKey')->equals($wrapped->getIdentifier());
        $queryBuilder->field('objectClass')->equals($objectClass);

        $query = $queryBuilder->getQuery();
        $query->execute();
    }

    public function insertTranslationRecord(TranslationInterface $translation): void
    {
        $meta = $this->manager->getClassMetadata(\get_class($translation));
        $collection = $this->manager->getDocumentCollection($meta->getName());
        $collection->insertOne($translation);
    }

    /**
     * @return mixed
     */
    public function getTranslationValue(WrapperInterface $wrapped, ClassMetadata $metadata, string $field)
    {
        $mapping = $metadata->getFieldMapping($field);

        $type = $this->getType($mapping['type']);
        $value = $wrapped->getPropertyValue($field);

        return $type->convertToDatabaseValue($value);
    }

    public function setTranslationValue(WrapperInterface $wrapped, ClassMetadata $metadata, string $field, $value): void
    {
        $mapping = $metadata->getFieldMapping($field);
        $type = $this->getType($mapping['type']);

        $value = $type->convertToPHPValue($value);
        $wrapped->setPropertyValue($field, $value);
    }

    private function getType(string $type): Type
    {
        return Type::getType($type);
    }
}

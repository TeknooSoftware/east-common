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

namespace Teknoo\East\Website\Doctrine\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Teknoo\East\Website\Doctrine\Exception\RuntimeException;
use Teknoo\East\Website\Doctrine\Translatable\Event\Adapter\ODM as EventOdm;
use Teknoo\East\Website\Doctrine\Translatable\Event\AdapterInterface as EventAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface as PersistenceAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\DocumentWrapper;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;

/**
 * The translation listener handles the generation and
 * loading of translations for entities which implements
 * the TranslatableInterface interface.
 *
 * This behavior can impact the performance of your application
 * since it does an additional query for each field to translate.
 *
 * Nevertheless the annotation metadata is properly cached and
 * it is not a big overhead to lookup all entity annotations since
 * the caching is activated for metadata
 */
class TranslatableListener implements EventSubscriber
{
    /**
     * ExtensionMetadataFactory used to read the extension
     * metadata through the extension drivers
     */
    private ExtensionMetadataFactory $extensionMetadataFactory;

    private ManagerAdapterInterface $manager;

    private PersistenceAdapterInterface $persistence;

    /**
     * Locale which is set on this listener.
     * If Entity being translated has locale defined it
     * will override this one
     */
    private string $locale;

    /**
     * Default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default. This
     * will load the default translation in other locales
     * if record is not translated yet
     */
    private string $defaultLocale;

    /**
     * If this is set to false, when if entity does
     * not have a translation for requested locale
     * it will show a blank value
     */
    private bool $translationFallback;

    /**
     * List of translations which do not have the foreign
     * key generated yet - MySQL case. These translations
     * will be updated with new keys on postPersist event
     */
    private array $pendingTranslationInserts = [];

    /**
     * Tracks locale the objects currently translated in
     */
    private array $translatedInLocale = [];

    /**
     * Tracks translation object for default locale
     * @var array<string, array<string, TranslationInterface>>
     */
    private array $translationInDefaultLocale = [];

    /**
     * Static List of cached object configurations
     * leaving it static for reasons to look into
     * other listener configuration
     */
    private array $configurations = array();

    public function __construct(
        ExtensionMetadataFactory $extensionMetadataFactory,
        ManagerAdapterInterface $manager,
        PersistenceAdapterInterface $persistence,
        string $locale = 'en_US',
        string $defaultLocale = 'en_US',
        bool $translationFallback = true
    ) {
        $this->extensionMetadataFactory = $extensionMetadataFactory;
        $this->manager = $manager;
        $this->persistence = $persistence;
        $this->locale = $locale;
        $this->defaultLocale = $defaultLocale;
        $this->translationFallback = $translationFallback;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
            'postLoad',
            'postPersist',
            'preFlush',

            'onFlush',
        ];
    }

    private function getEventAdapter(LifecycleEventArgs $eventArgs): EventAdapterInterface
    {
        //todo
        return new EventOdm($eventArgs);
    }

    private function wrap(TranslatableInterface $translatable): WrapperInterface
    {
        return new DocumentWrapper($translatable, $this->manager->getRootObject());
    }

    private function loadMetadataForObjectClass(ClassMetadata $metadata): array
    {
        return $this->extensionMetadataFactory->getExtensionMetadata($this->manager->getRootObject(), $metadata);
    }

    private function getConfiguration(ClassMetadata $meta): array
    {
        $className = $meta->getName();
        if (isset($this->configurations[$className])) {
            return $this->configurations[$className];
        }

        $this->configurations[$className] = $this->loadMetadataForObjectClass($meta);

        return $this->configurations[$className];
    }

    public function loadClassMetadata(LifecycleEventArgs $eventArgs): void
    {
        $event = $this->getEventAdapter($eventArgs);
        $classMetaData = $this->manager->getClassMetadata($event->getObjectClass());

        $this->configurations[$classMetaData->getName()] =  $this->loadMetadataForObjectClass($classMetaData);
    }

    /*
     * Gets the locale to use for translation. Loads object
     * defined locale first..
     */
    private function getTranslatableLocale(
        ClassMetadata $metaClass,
        string $localePropertyName,
        TranslatableInterface $object
    ): string {
        $locale = $this->locale;

        $reflectionClass = $metaClass->getReflectionClass();
        $className = $metaClass->getName();

        try {
            $reflectionProperty = $reflectionClass->getProperty($localePropertyName);
        } catch (\Throwable $error) {
            throw new RuntimeException(
                "There is no locale or language property ({$localePropertyName}) found on object: {$className}",
                0,
                $error
            );
        }

        $reflectionProperty->setAccessible(true);
        $value = (string) $reflectionProperty->getValue($object);

        if (!empty($value)) {
            $locale = $value;
        }

        return $locale;
    }

    /*
     * After object is loaded, listener updates the translations by currently used locale
     */
    public function postLoad(LifecycleEventArgs $eventArgs): void
    {
        $event = $this->getEventAdapter($eventArgs);
        $object = $event->getObject();
        $metaClass = $this->manager->getClassMetadata($event->getObjectClass());

        $config = $this->getConfiguration($metaClass);
        if (!isset($config['fields'], $config['locale'])) {
            return;
        }

        $locale = $this->getTranslatableLocale($metaClass, $config['locale'], $object);
        $oid = \spl_object_hash($object);
        $this->translatedInLocale[$oid] = $locale;

        if ($locale === $this->defaultLocale) {
            return;
        }

        // fetch translations
        $translationClass = $config['translationClass'];
        $wrapper = $this->wrap($object);

        $result = $this->persistence->loadTranslations(
            $wrapper,
            $translationClass,
            $locale,
            $config['useObjectClass']
        );
        
        // translate object's translatable properties
        foreach ($config['fields'] as $field) {
            $translated = '';
            $isTranslated = false;
            foreach ((array) $result as $entry) {
                if ($entry['field'] === $field) {
                    $translated = $entry['content'] ?? null;
                    $isTranslated = true;
                    break;
                }
            }

            // update translation
            if (
                $isTranslated
                || (!$this->translationFallback && empty($config['fallback'][$field]))
            ) {
                $this->persistence->setTranslationValue($wrapper, $field, $translated);
                // ensure clean changeset
                $this->manager->setOriginalObjectProperty(
                    $oid,
                    $field,
                    $metaClass->getReflectionProperty($field)->getValue($object)
                );
            }
        }
    }

    /*
     * Checks for inserted object to update their translation foreign keys
     */
    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $event = $this->getEventAdapter($eventArgs);
        $object = $event->getObject();

        $oid = \spl_object_hash($object);

        if (!isset($this->pendingTranslationInserts[$oid])) {
            return;
        }

        $wrapper = $this->wrap($object);
        // load the pending translations without key
        $objectId = $wrapper->getIdentifier();
        foreach ($this->pendingTranslationInserts[$oid] as $translation) {
            $translation->setForeignKey($objectId);
            $this->persistence->insertTranslationRecord($translation);
        }
        unset($this->pendingTranslationInserts[$oid]);
    }

    /*
     * Handle translation changes in default locale
     *
     * This has to be done in the preFlush because, when an entity has been loaded
     * in a different locale, no changes will be detected.
     */
    public function preFlush()
    {
        foreach ($this->translationInDefaultLocale as $oid => $fields) {
            $trans = \reset($fields);

            $object = $this->manager->tryGetById($trans->getForeignKey(), $trans->getObjectClass());

            if (!$object) {
                continue;
            }

            $this->manager->scheduleForUpdate($object);
        }
    }


    /**
     * Creates the translation for object being flushed
     *
     * @param TranslatableAdapter $ea
     * @param object              $object
     * @param boolean             $isInsert
     *
     * @throws \UnexpectedValueException - if locale is not valid, or
     *                                   primary key is composite, missing or invalid
     */
    private function handleTranslatableObjectUpdate(EventAdapterInterface $adapter, $object, $isInsert)
    {
        $om = $ea->getObjectManager();
        $wrapped = new MongoDocumentWrapper($object, $om);
        $meta = $wrapped->getMetadata();
        $config = $this->getConfiguration($om, $meta->name);
        // no need cache, metadata is loaded only once in MetadataFactoryClass
        $translationClass = $this->getTranslationClass($ea, $config['useObjectClass']);
        $translationMetadata = $om->getClassMetadata($translationClass);

        // check for the availability of the primary key
        $objectId = $wrapped->getIdentifier();
        // load the currently used locale
        $locale = $this->getTranslatableLocale($object, $meta, $om);

        $uow = $om->getUnitOfWork();
        $oid = spl_object_hash($object);
        $changeSet = $ea->getObjectChangeSet($uow, $object);
        $translatableFields = $config['fields'];
        foreach ($translatableFields as $field) {
            $wasPersistedSeparetely = false;
            $skip = isset($this->translatedInLocale[$oid]) && $locale === $this->translatedInLocale[$oid];
            $skip = $skip && !isset($changeSet[$field]) && !$this->getTranslationInDefaultLocale($oid, $field);
            if ($skip) {
                continue; // locale is same and nothing changed
            }
            $translation = null;
            foreach ($ea->getScheduledObjectInsertions($uow) as $trans) {
                if ($locale !== $this->defaultLocale
                    && get_class($trans) === $translationClass
                    && $trans->getLocale() === $this->defaultLocale
                    && $trans->getField() === $field
                    && $this->belongsToObject($ea, $trans, $object)) {
                    $this->setTranslationInDefaultLocale($oid, $field, $trans);
                    break;
                }
            }

            // lookup persisted translations
            foreach ($ea->getScheduledObjectInsertions($uow) as $trans) {
                if (get_class($trans) !== $translationClass
                    || $trans->getLocale() !== $locale
                    || $trans->getField() !== $field) {
                    continue;
                }

                $wasPersistedSeparetely = $trans->getObjectClass() === $config['useObjectClass']
                    && $trans->getForeignKey() === $objectId;

                if ($wasPersistedSeparetely) {
                    $translation = $trans;
                    break;
                }
            }

            // check if translation already is created
            if (!$isInsert && !$translation) {
                $translation = $ea->findTranslation(
                    $wrapped,
                    $locale,
                    $field,
                    $translationClass,
                    $config['useObjectClass']
                );
            }

            // create new translation if translation not already created and locale is different from default locale, otherwise, we have the date in the original record
            $persistNewTranslation = !$translation
                && ($locale !== $this->defaultLocale || $this->persistDefaultLocaleTranslation)
            ;
            if ($persistNewTranslation) {
                $translation = $translationMetadata->newInstance();
                $translation->setLocale($locale);
                $translation->setField($field);
                $translation->setObjectClass($config['useObjectClass']);
                $translation->setForeignKey($objectId);
            }

            if ($translation) {
                // set the translated field, take value using reflection
                $content = $ea->getTranslationValue($object, $field);
                $translation->setContent($content);
                // check if need to update in database
                $transWrapper = new MongoDocumentWrapper($translation, $om);
                if (((is_null($content) && !$isInsert) || is_bool($content) || is_int($content) || is_string($content) || !empty($content)) && ($isInsert || !$transWrapper->getIdentifier() || isset($changeSet[$field]))) {
                    if ($isInsert && !$objectId) {
                        // if we do not have the primary key yet available
                        // keep this translation in memory to insert it later with foreign key
                        $this->pendingTranslationInserts[spl_object_hash($object)][] = $translation;
                    } else {
                        // persist and compute change set for translation
                        if ($wasPersistedSeparetely) {
                            $ea->recomputeSingleObjectChangeset($uow, $translationMetadata, $translation);
                        } else {
                            $om->persist($translation);
                            $uow->computeChangeSet($translationMetadata, $translation);
                        }
                    }
                }
            }

            if ($isInsert && $this->getTranslationInDefaultLocale($oid, $field) !== null) {
                // We can't rely on object field value which is created in non-default locale.
                // If we provide translation for default locale as well, the latter is considered to be trusted
                // and object content should be overridden.
                $wrapped->setPropertyValue($field, $this->getTranslationInDefaultLocale($oid, $field)->getContent());
                $ea->recomputeSingleObjectChangeset($uow, $meta, $object);
                $this->removeTranslationInDefaultLocale($oid, $field);
            }
        }
        $this->translatedInLocale[$oid] = $locale;
        // check if we have default translation and need to reset the translation
        if (!$isInsert && strlen($this->defaultLocale)) {
            $this->validateLocale($this->defaultLocale);
            $modifiedChangeSet = $changeSet;
            foreach ($changeSet as $field => $changes) {
                if (in_array($field, $translatableFields)) {
                    if ($locale !== $this->defaultLocale) {
                        $ea->setOriginalObjectProperty($uow, $oid, $field, $changes[0]);
                        unset($modifiedChangeSet[$field]);
                    }
                }
            }
            $ea->recomputeSingleObjectChangeset($uow, $meta, $object);
            // cleanup current changeset only if working in a another locale different than de default one, otherwise the changeset will always be reverted
            if ($locale !== $this->defaultLocale) {
                $ea->clearObjectChangeSet($uow, $oid);
                // recompute changeset only if there are changes other than reverted translations
                if ($modifiedChangeSet || $this->hasTranslationsInDefaultLocale($oid)) {
                    foreach ($modifiedChangeSet as $field => $changes) {
                        $ea->setOriginalObjectProperty($uow, $oid, $field, $changes[0]);
                    }
                    foreach ($translatableFields as $field) {
                        if ($this->getTranslationInDefaultLocale($oid, $field) !== null) {
                            $wrapped->setPropertyValue($field, $this->getTranslationInDefaultLocale($oid, $field)->getContent());
                            $this->removeTranslationInDefaultLocale($oid, $field);
                        }
                    }
                    $ea->recomputeSingleObjectChangeset($uow, $meta, $object);
                }
            }
        }
    }

    /*
     * Looks for translatable objects being inserted or updated for further processing
     */
    public function onFlush(LifecycleEventArgs $eventArgs): void
    {
        $event =  $this->getEventAdapter($eventArgs);

        $handling = function ($object, $isInsert) use ($event) {
            $metaClass = $this->manager->getClassMetadata($event->getObjectClass());
            $config = $this->getConfiguration($metaClass);

            if (isset($config['fields'])) {
                $this->handleTranslatableObjectUpdate($event, $object, $isInsert);
            }
        };

        // check all scheduled inserts for TranslatableInterface objects
        foreach ($this->manager->getScheduledObjectInsertions() as $object) {
            $handling($object, true);
        }

        // check all scheduled updates for TranslatableInterface entities
        foreach ($this->manager->getScheduledObjectUpdates() as $object) {
            $handling($object, false);
        }

        // check scheduled deletions for TranslatableInterface entities
        foreach ($this->manager->getScheduledObjectDeletions() as $object) {
            $metaClass = $this->manager->getClassMetadata($event->getObjectClass());
            $config = $this->getConfiguration($metaClass);

            if (isset($config['fields'])) {
                $wrapper = $this->wrap($object);
                $this->persistence->removeAssociatedTranslations(
                    $wrapper,
                    $config['translationClass'],
                    $config['useObjectClass']
                );
            }
        }
    }

    /////////////
    /////////////
    /////////////
    /////////////
    /////////////
    /////////////
    /////////////
    ///










    /**
     * Sets translation object which represents translation in default language.
     *
     * @param string $oid   hash of basic entity
     * @param string $field field of basic entity
     * @param mixed  $trans Translation object
     */
    private function setTranslationInDefaultLocale($oid, $field, $trans)
    {
        if (!isset($this->translationInDefaultLocale[$oid])) {
            $this->translationInDefaultLocale[$oid] = array();
        }
        $this->translationInDefaultLocale[$oid][$field] = $trans;
    }


    /**
     * Removes translation object which represents translation in default language.
     * This is for internal use only.
     *
     * @param string $oid   hash of the basic entity
     * @param string $field field of basic entity
     */
    private function removeTranslationInDefaultLocale($oid, $field)
    {
        if (isset($this->translationInDefaultLocale[$oid])) {
            if (isset($this->translationInDefaultLocale[$oid][$field])) {
                unset($this->translationInDefaultLocale[$oid][$field]);
            }
            if (! $this->translationInDefaultLocale[$oid]) {
                // We removed the final remaining elements from the
                // translationInDefaultLocale[$oid] array, so we might as well
                // completely remove the entry at $oid.
                unset($this->translationInDefaultLocale[$oid]);
            }
        }
    }

    /**
     * Gets translation object which represents translation in default language.
     * This is for internal use only.
     *
     * @param string $oid   hash of the basic entity
     * @param string $field field of basic entity
     *
     * @return mixed Returns translation object if it exists or NULL otherwise
     */
    private function getTranslationInDefaultLocale($oid, $field)
    {
        if (array_key_exists($oid, $this->translationInDefaultLocale)) {
            if (array_key_exists($field, $this->translationInDefaultLocale[$oid])) {
                $ret = $this->translationInDefaultLocale[$oid][$field];
            } else {
                $ret = null;
            }
        } else {
            $ret = null;
        }

        return $ret;
    }

    /**
     * Check if object has any translation object which represents translation in default language.
     * This is for internal use only.
     *
     * @param string $oid hash of the basic entity
     *
     * @return bool
     */
    private function hasTranslationsInDefaultLocale($oid)
    {
        return array_key_exists($oid, $this->translationInDefaultLocale);
    }

    /**
     * Checks if the translation entity belongs to the object in question
     *
     * @param TranslatableAdapter $ea
     * @param object              $trans
     * @param object              $object
     *
     * @return boolean
     */
    private function belongsToObject($trans, $object)
    {
        return ($trans->getForeignKey() === $object->getId()
            && ($trans->getObjectClass() === get_class($object)));
    }
}

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
        ClassMetadata $metaData,
        string $localePropertyName,
        TranslatableInterface $object
    ): string {
        $locale = $this->locale;

        $reflectionClass = $metaData->getReflectionClass();
        $className = $metaData->getName();

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
        $metaData = $this->manager->getClassMetadata($event->getObjectClass());

        $config = $this->getConfiguration($metaData);
        if (!isset($config['fields'], $config['locale'])) {
            return;
        }

        $locale = $this->getTranslatableLocale($metaData, $config['locale'], $object);
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
                $this->persistence->setTranslationValue($wrapper, $metaData, $field, $translated);
                // ensure clean changeset
                $this->manager->setOriginalObjectProperty(
                    $oid,
                    $field,
                    $metaData->getReflectionProperty($field)->getValue($object)
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

    /*
     * Checks if the translation entity belongs to the object in question
     */
    private function belongsToObject(
        TranslationInterface $trans,
        TranslatableInterface $object
    ): bool {
        return ($trans->getForeignKey() === $object->getId()
            && \is_a($object, $trans->getObjectClass()));
    }

    /*
     * Sets translation object which represents translation in default language.
     */
    private function setTranslationInDefaultLocale(string $oid, string $field, TranslationInterface $trans): void
    {
        $this->translationInDefaultLocale[$oid][$field] = $trans;
    }

    /*
     * Removes translation object which represents translation in default language.
     * This is for internal use only.
     */
    private function removeTranslationInDefaultLocale(string $oid, string $field): void
    {
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

    /*
     * Check if object has any translation object which represents translation in default language.
     * This is for internal use only.
     */
    private function hasTranslationsInDefaultLocale(string $oid): bool
    {
        return \array_key_exists($oid, $this->translationInDefaultLocale);
    }

    /*
     * Gets translation object which represents translation in default language.
     * This is for internal use only.
     */
    private function getTranslationInDefaultLocale(string $oid, string $field): ?TranslationInterface
    {
        if (isset($this->translationInDefaultLocale[$oid][$field])) {
            return $this->translationInDefaultLocale[$oid][$field];
        }

        return null;
    }

    /*
     * Creates the translation for object being flushed
     */
    private function handleTranslatableObjectUpdate(
        EventAdapterInterface $event,
        TranslatableInterface $object,
        bool $isInsert
    ) {
        $wrapper = $this->wrap($object);
        $metaData = $this->manager->getClassMetadata($event->getObjectClass());
        $config = $this->getConfiguration($metaData);

        $translationClass = $config['translationClass'];
        $translationMetadata = $this->manager->getClassMetadata($translationClass);
        $translationReflection = $translationMetadata->getReflectionClass();

        // check for the availability of the primary key
        $objectId = $wrapper->getIdentifier();
        $oid = \spl_object_hash($object);

        // load the currently used locale
        $locale = $this->getTranslatableLocale($metaData, $config['locale'], $object);

        $changeSet = $this->manager->getObjectChangeSet($object);

        //todo check
        $translatableFields = $config['fields'];
        foreach ($translatableFields as $field) {
            $wasPersistedSeparetely = false;
            //what
            $skip = isset($this->translatedInLocale[$oid]) && $locale === $this->translatedInLocale[$oid];
            $skip = $skip && !isset($changeSet[$field]) && !$this->getTranslationInDefaultLocale($oid, $field);
            if ($skip) {
                continue; // locale is same and nothing changed
            }

            //todo optimize
            $translation = null;
            foreach ($this->manager->getScheduledObjectInsertions() as $trans) {
                if ($locale !== $this->defaultLocale
                    && $translationReflection->isInstance($trans)
                    && $trans->getLocale() === $this->defaultLocale
                    && $trans->getField() === $field
                    && $this->belongsToObject($trans, $object)) {

                    //todo Why
                    $this->setTranslationInDefaultLocale($oid, $field, $trans);
                    break;
                }
            }

            //todo Why ??
            // lookup persisted translations
            foreach ($this->manager->getScheduledObjectInsertions() as $trans) {
                if (!$translationReflection->isInstance($trans)
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
            //todo why
            if (!$isInsert && !$translation instanceof TranslationInterface) {
                $translation = $this->persistence->findTranslation(
                    $wrapper,
                    $locale,
                    $field,
                    $translationClass,
                    $config['useObjectClass']
                );
            }

            // create new translation if translation not already created and locale is different from default locale, otherwise, we have the date in the original record
            if (!$translation instanceof TranslationInterface && $locale !== $this->defaultLocale) {
                //todo
                $translation = $translationMetadata->newInstance();
                $translation->setLocale($locale);
                $translation->setField($field);
                $translation->setObjectClass($config['useObjectClass']);
                $translation->setForeignKey($objectId);
            }

            // set the translated field, take value using reflection
            $content = $this->persistence->getTranslationValue($wrapper, $metaData, $field);
            $translation->setContent($content);
            // check if need to update in database
            if (
                (
                    (empty($content) && !$isInsert)
                    || !empty($content)
                )
                && (
                    $isInsert
                    || !empty($translation->getIdentifier())
                    || isset($changeSet[$field])
                )
            ) {
                if ($isInsert && empty($objectId)) {
                    //todo wheck
                    // if we do not have the primary key yet available
                    // keep this translation in memory to insert it later with foreign key
                    $this->pendingTranslationInserts[$oid][] = $translation;
                } else {
                    // persist and compute change set for translation
                    if ($wasPersistedSeparetely) {
                        $this->manager->recomputeSingleObjectChangeset($translationMetadata, $translation);
                    } else {
                        $this->manager->persist($translation);
                        $this->manager->computeChangeSet($translationMetadata, $translation);
                    }
                }
            }

            if ($isInsert && null !== $this->getTranslationInDefaultLocale($oid, $field)) {
                //todo why
                // We can't rely on object field value which is created in non-default locale.
                // If we provide translation for default locale as well, the latter is considered to be trusted
                // and object content should be overridden.
                $wrapper->setPropertyValue($field, $this->getTranslationInDefaultLocale($oid, $field)->getContent());
                $this->manager->recomputeSingleObjectChangeset($metaData, $object);
                $this->removeTranslationInDefaultLocale($oid, $field);
            }
        }

        $this->translatedInLocale[$oid] = $locale;

        // check if we have default translation and need to reset the translation
        if (!$isInsert) {
            $modifiedChangeSet = $changeSet;
            foreach ($changeSet as $field => $changes) {
                //todo why : $translatableFields ??
                if (\in_array($field, $translatableFields)) {
                    if ($locale !== $this->defaultLocale) {
                        $this->manager->setOriginalObjectProperty($oid, $field, $changes[0]);
                        unset($modifiedChangeSet[$field]);
                    }
                }
            }

            $this->manager->recomputeSingleObjectChangeset($metaData, $object);
            // cleanup current changeset only if working in a another locale different than de default one,
            // otherwise the changeset will always be reverted
            if ($locale !== $this->defaultLocale) {
                $this->manager->clearObjectChangeSet($oid);
                // recompute changeset only if there are changes other than reverted translations
                if ($modifiedChangeSet || $this->hasTranslationsInDefaultLocale($oid)) {
                    foreach ($modifiedChangeSet as $field => $changes) {
                        $this->manager->setOriginalObjectProperty($oid, $field, $changes[0]);
                    }

                    foreach ($translatableFields as $field) {
                        if (null !== $this->getTranslationInDefaultLocale($oid, $field)) {
                            $wrapper->setPropertyValue($field, $this->getTranslationInDefaultLocale($oid, $field)->getContent());
                            $this->removeTranslationInDefaultLocale($oid, $field);
                        }
                    }

                    $this->manager->recomputeSingleObjectChangeset($metaData, $object);
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
            $metaData = $this->manager->getClassMetadata($event->getObjectClass());
            $config = $this->getConfiguration($metaData);

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
            $metaData = $this->manager->getClassMetadata($event->getObjectClass());
            $config = $this->getConfiguration($metaData);

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
}

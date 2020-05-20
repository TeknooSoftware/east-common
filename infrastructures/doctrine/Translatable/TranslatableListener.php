<?php

namespace Teknoo\East\Website\Doctrine\Translatable;

use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Teknoo\East\Website\Doctrine\Event\Adapter\ODM;
use Teknoo\East\Website\Doctrine\Event\AdapterInterface;
use Teknoo\East\Website\Doctrine\Exception\RuntimeException;
use Teknoo\East\Website\Doctrine\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Tool\Wrapper\MongoDocumentWrapper;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Event\TranslatableAdapter;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\EventArgs;

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
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class TranslatableListener implements EventSubscriber
{
    /**
     * Locale which is set on this listener.
     * If Entity being translated has locale defined it
     * will override this one
     *
     * @var string
     */
    private $locale = 'en_US';

    /**
     * Default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default. This
     * will load the default translation in other locales
     * if record is not translated yet
     *
     * @var string
     */
    private $defaultLocale = 'en_US';

    /**
     * If this is set to false, when if entity does
     * not have a translation for requested locale
     * it will show a blank value
     *
     * @var boolean
     */
    private $translationFallback = true;

    /**
     * List of translations which do not have the foreign
     * key generated yet - MySQL case. These translations
     * will be updated with new keys on postPersist event
     *
     * @var array
     */
    private $pendingTranslationInserts = array();

    /**
     * Currently in case if there is TranslationQueryWalker
     * in charge. We need to skip issuing additional queries
     * on load
     *
     * @var boolean
     */
    private $skipOnLoad = false;

    /**
     * Tracks locale the objects currently translated in
     *
     * @var array
     */
    private $translatedInLocale = array();

    /**
     * Whether or not, to persist default locale
     * translation or keep it in original record
     *
     * @var boolean
     */
    private $persistDefaultLocaleTranslation = false;

    /**
     * Tracks translation object for default locale
     *
     * @var array
     */
    private $translationInDefaultLocale = array();
    /**
     * Static List of cached object configurations
     * leaving it static for reasons to look into
     * other listener configuration
     *
     * @var array
     */
    private static $configurations = array();

    /**
     * ExtensionMetadataFactory used to read the extension
     * metadata through the extension drivers
     *
     * @var ExtensionMetadataFactory
     */
    private $extensionMetadataFactory = array();

    private function getAdapter(EventArgs $eventArgs): AdapterInterface
    {
        //todo
        return new ODM($eventArgs);
    }

    private function getConfiguration(ObjectManager $objectManager, string $class): array
    {
        $config = array();
        if (isset(static::$configurations[$class])) {
            $config = static::$configurations[$class];
        }

        $factory = $objectManager->getMetadataFactory();
        $cacheDriver = $factory->getCacheDriver();

        if ($cacheDriver) {
            //todo
            $cacheId = ExtensionMetadataFactory::getCacheId($class, $this->getNamespace());
            if (($cached = $cacheDriver->fetch($cacheId)) !== false) {
                static::$configurations[$class] = $cached;
                $config = $cached;
            } else {
                // re-generate metadata on cache miss
                $this->loadMetadataForObjectClass($objectManager, $factory->getMetadataFor($class));
                if (isset(static::$configurations[$class])) {
                    $config = static::$configurations[$class];
                }
            }

            $objectClass = isset($config['useObjectClass']) ? $config['useObjectClass'] : $class;
            if ($objectClass !== $class) {
                $this->getConfiguration($objectManager, $objectClass);
            }
        }

        return $config;
    }

    private function getExtensionMetadataFactory(ObjectManager $objectManager): ExtensionMetadataFactory
    {
        //todo
        $oid = spl_object_hash($objectManager);
        if (!isset($this->extensionMetadataFactory[$oid])) {
            $this->extensionMetadataFactory[$oid] = new ExtensionMetadataFactory(
                $objectManager,
                $this->getNamespace()
            );
        }

        return $this->extensionMetadataFactory[$oid];
    }

    private function loadMetadataForObjectClass(ObjectManager $objectManager, ClassMetadata $metadata): void
    {
        $factory = $this->getExtensionMetadataFactory($objectManager);

        try {
            $config = $factory->getExtensionMetadata($metadata);

            if ($config) {
                static::$configurations[$metadata->name] = $config;
            }
        } catch (\ReflectionException $e) {
            // entity\document generator is running
            // will not store a cached version, to remap later
        }
    }

    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
            'postPersist',
            'preFlush',
            'onFlush',
            'loadClassMetadata',
        ];
    }

    public function loadClassMetadata(EventArgs $eventArgs): void
    {
        $adapter = $this->getAdapter($eventArgs);
        $this->loadMetadataForObjectClass($adapter->getObjectManager(), $adapter->getClassMetadata());
    }

    private function getTranslationClass(TranslatableAdapter $adapter, string $class): string
    {
        return static::$configurations[$class]['translationClass'] ?? $adapter->getDefaultTranslationClass();
    }

    public function setTranslationFallback(bool $bool): self
    {
        $this->translationFallback = (bool) $bool;

        return $this;
    }

    /**
     * Set the locale to use for translation listener
     */
    public function setTranslatableLocale(string $locale): self
    {
        $this->validateLocale($locale);
        $this->locale = $locale;

        return $this;
    }

    /**
     * Sets the default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default
     */
    public function setDefaultLocale(string $locale): self
    {
        $this->validateLocale($locale);
        $this->defaultLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale to use for translation. Loads object
     * defined locale first..
     */
    private function getTranslatableLocale(
        TranslatableInterface $object,
        ClassMetadata $meta,
        ?ObjectManager $om = null
    ): string {
        $locale = $this->locale;

        if (isset(static::$configurations[$meta->name]['locale'])) {

            $class = $meta->getReflectionClass();
            $reflectionProperty = $class->getProperty(static::$configurations[$meta->name]['locale']);
            if (!$reflectionProperty) {
                $column = static::$configurations[$meta->name]['locale'];
                throw new RuntimeException("There is no locale or language property ({$column}) found on object: {$meta->name}");
            }

            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($object);

            if (\is_object($value) && \method_exists($value, '__toString')) {
                $value = (string) $value;
            }

            if ($this->isValidLocale($value)) {
                $locale = $value;
            }

            return $locale;
        }

        if ($om instanceof DocumentManager) {
            [, $parentObject] = $om->getUnitOfWork()->getParentAssociation($object);
            if (null !== $parentObject) {
                $parentMeta = $om->getClassMetadata(\get_class($parentObject));
                $locale = $this->getTranslatableLocale($parentObject, $parentMeta, $om);
            }
        }

        return $locale;
    }

    /**
     * Handle translation changes in default locale
     *
     * This has to be done in the preFlush because, when an entity has been loaded
     * in a different locale, no changes will be detected.
     */
    public function preFlush(EventArgs $args)
    {
        $adapter =  $this->getAdapter($args);

        foreach ($this->translationInDefaultLocale as $oid => $fields) {
            $trans = \reset($fields);

            $object = $adapter->tryGetById($trans->getForeignKey(), $trans->getObjectClass());

            if (!$object) {
                continue;
            }

            $adapter->scheduleForUpdate($object);
        }
    }

    /**
     * Looks for translatable objects being inserted or updated
     * for further processing
     */
    public function onFlush(EventArgs $args): void
    {
        $adapter =  $this->getAdapter($args);

        $om = $adapter->getObjectManager();

        // check all scheduled inserts for TranslatableInterface objects
        foreach ($adapter->getScheduledObjectInsertions() as $object) {
            $meta = $adapter->getClassMetadata(\get_class($object));
            $config = $this->getConfiguration($om, $meta->name);

            if (isset($config['fields'])) {
                $this->handleTranslatableObjectUpdate($adapter, $object, true);
            }
        }

        // check all scheduled updates for TranslatableInterface entities
        foreach ($adapter->getScheduledObjectUpdates() as $object) {
            $meta = $om->getClassMetadata(\get_class($object));
            $config = $this->getConfiguration($om, $meta->name);

            if (isset($config['fields'])) {
                $this->handleTranslatableObjectUpdate($adapter, $object, false);
            }
        }

        // check scheduled deletions for TranslatableInterface entities
        foreach ($adapter->getScheduledObjectDeletions() as $object) {
            $meta = $om->getClassMetadata(\get_class($object));
            $config = $this->getConfiguration($om, $meta->name);

            if (isset($config['fields'])) {
                $wrapped = new MongoDocumentWrapper($object, $om);
                $transClass = $this->getTranslationClass($adapter, $meta->name);
                $adapter->removeAssociatedTranslations($wrapped, $transClass, $config['useObjectClass']);
            }
        }
    }

     /**
     * Checks for inserted object to update their translation
     * foreign keys
     *
     * @param EventArgs $args
     */
    public function postPersist(EventArgs $args)
    {
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        // check if entity is tracked by translatable and without foreign key
        if ($this->getConfiguration($om, $meta->name) && count($this->pendingTranslationInserts)) {
            $oid = spl_object_hash($object);
            if (array_key_exists($oid, $this->pendingTranslationInserts)) {
                // load the pending translations without key
                $wrapped = new MongoDocumentWrapper($object, $om);
                $objectId = $wrapped->getIdentifier();
                foreach ($this->pendingTranslationInserts[$oid] as $translation) {
                    $translation->setForeignKey($objectId);
                    $ea->insertTranslationRecord($translation);
                }
                unset($this->pendingTranslationInserts[$oid]);
            }
        }
    }

    /**
     * After object is loaded, listener updates the translations
     * by currently used locale
     *
     * @param EventArgs $args
     */
    public function postLoad(EventArgs $args)
    {
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);
        if (isset($config['fields'])) {
            $locale = $this->getTranslatableLocale($object, $meta, $om);
            $oid = spl_object_hash($object);
            $this->translatedInLocale[$oid] = $locale;
        }

        if ($this->skipOnLoad) {
            return;
        }

        if (isset($config['fields']) && ($locale !== $this->defaultLocale || $this->persistDefaultLocaleTranslation)) {
            // fetch translations
            $translationClass = $this->getTranslationClass($ea, $config['useObjectClass']);
            $result = $ea->loadTranslations(
                $object,
                $translationClass,
                $locale,
                $config['useObjectClass']
            );
            // translate object's translatable properties
            foreach ($config['fields'] as $field) {
                $translated = '';
                $is_translated = false;
                foreach ((array) $result as $entry) {
                    if ($entry['field'] == $field) {
                        $translated = isset($entry['content']) ? $entry['content'] : null;
                        $is_translated = true;
                        break;
                    }
                }
                // update translation
                if ($is_translated
                    || (!$this->translationFallback && (!isset($config['fallback'][$field]) || !$config['fallback'][$field]))
                    || ($this->translationFallback && isset($config['fallback'][$field]) && !$config['fallback'][$field])
                ) {
                    $ea->setTranslationValue($object, $field, $translated);
                    // ensure clean changeset
                    $ea->setOriginalObjectProperty(
                        $om->getUnitOfWork(),
                        $oid,
                        $field,
                        $meta->getReflectionProperty($field)->getValue($object)
                    );
                }
            }
        }
    }

    /**
     * Validates the given locale
     *
     * @param string $locale - locale to validate
     *
     * @throws \Teknoo\East\Website\Doctrine\Exception\InvalidArgumentException if locale is not valid
     */
    private function validateLocale($locale)
    {
        if (!$this->isValidLocale($locale)) {
            throw new \Teknoo\East\Website\Doctrine\Exception\InvalidArgumentException('Locale or language cannot be empty and must be set through Listener or Entity');
        }
    }

    /**
     * Check if the given locale is valid
     *
     * @param string $locale - locale to check
     *
     * @return bool
     */
    private function isValidlocale($locale)
    {
        return is_string($locale) && strlen($locale);
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
    private function handleTranslatableObjectUpdate(AdapterInterface $adapter, $object, $isInsert)
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

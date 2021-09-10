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
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as ClassMetadataODM;
use DomainException;
use ProxyManager\Proxy\GhostObjectInterface;
use RuntimeException;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface as PersistenceAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\FactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

use function array_flip;
use function get_parent_class;
use function spl_object_hash;

/**
 * The translation listener handles the generation and
 * loading of translations for object which implements
 * the TranslatableInterface interface.
 *
 * This behavior can impact the performance of your application
 * since it does an additional query for each field to translate.
 *
 * Nevertheless the xml metadata is properly cached and
 * it is not a big overhead to lookup all objects mapping since
 * the caching is activated for metadata
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @author      Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
class TranslatableListener implements EventSubscriber
{
    /*
     * ExtensionMetadataFactory used to read the extension
     * metadata through the extension drivers
     */
    private ExtensionMetadataFactory $extensionMetadataFactory;

    private ManagerAdapterInterface $manager;

    private PersistenceAdapterInterface $persistence;

    private FactoryInterface $wrapperFactory;

    /*
     * Locale which is set on this listener.
     * If pbject being translated has locale defined it
     * will override this one
     */
    private string $locale;

    /*
     * Default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default. This
     * will load the default translation in other locales
     * if record is not translated yet
     */
    private string $defaultLocale;

    /*
     * If this is set to false, when if object does
     * not have a translation for requested locale
     * it will show a blank value
     */
    private bool $translationFallback;

    /**
     * List of translations which do not have the foreign
     * key generated yet - MySQL case. These translations
     * will be updated with new keys on postPersist event
     * @var array<string, array<int, TranslationInterface>>
     */
    private array $pendingTranslationInserts = [];

    /**
     * Tracks objects to reload after flush
     * @var array<string, mixed>
     */
    private array $objectsToTranslate = [];

    /**
     * List of cached object configurations leaving it static for reasons to look into
     * other listener configuration.
     * @var array<string, mixed>
     */
    private array $configurations = array();

    /**
     * List of cached class metadata from doctrine manager
     * @var array<string, ClassMetadataODM>
     */
    private array $classMetadata = [];

    public function __construct(
        ExtensionMetadataFactory $extensionMetadataFactory,
        ManagerAdapterInterface $manager,
        PersistenceAdapterInterface $persistence,
        FactoryInterface $wrapperFactory,
        string $locale = 'en',
        string $defaultLocale = 'en',
        bool $translationFallback = true
    ) {
        $this->extensionMetadataFactory = $extensionMetadataFactory;
        $this->manager = $manager;
        $this->persistence = $persistence;
        $this->wrapperFactory = $wrapperFactory;
        $this->locale = $locale;
        $this->defaultLocale = $defaultLocale;
        $this->translationFallback = $translationFallback;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
            'postLoad',
            'onFlush',
            'postPersist',
            'postFlush',
        ];
    }

    /**
     * @return ClassMetadata<ClassMetadataODM>
     */
    private function getClassMetadata(string $className): ClassMetadata
    {
        if (isset($this->classMetadata[$className])) {
            return $this->classMetadata[$className];
        }

        $this->manager->findClassMetadata($className, $this);

        if (isset($this->classMetadata[$className])) {
            return $this->classMetadata[$className];
        }

        throw new DomainException("Error no classmeta data available for $className");
    }

    /**
     * @param ClassMetadataODM $classMetadata
     */
    public function registerClassMetadata(string $className, ClassMetadata $classMetadata): self
    {
        $this->classMetadata[$className] = $classMetadata;

        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    private function getObjectClassName(TranslatableInterface $object): string
    {
        if ($object instanceof GhostObjectInterface) {
            return (string) get_parent_class($object);
        }

        return $object::class;
    }

    /**
     * @param ClassMetadata<ClassMetadataODM> $metadata
     */
    private function wrap(TranslatableInterface $translatable, ClassMetadata $metadata): WrapperInterface
    {
        return ($this->wrapperFactory)($translatable, $metadata);
    }

    /**
     * @param ClassMetadata<ClassMetadataODM> $metadata
     */
    private function loadMetadataForObjectClass(ClassMetadata $metadata): void
    {
        $this->extensionMetadataFactory->loadExtensionMetadata($metadata, $this);
    }

    /**
     * @param ClassMetadata<ClassMetadataODM> $metadata
     * @param array<string, mixed> $config
     */
    public function injectConfiguration(ClassMetadata $metadata, array $config): self
    {
        $className = $metadata->getName();

        $this->configurations[$className] = $config;

        return $this;
    }

    /**
     * @param ClassMetadata<ClassMetadataODM> $metadata
     * @return array<string, mixed>
     */
    private function getConfiguration(ClassMetadata $metadata): array
    {
        $className = $metadata->getName();
        if (isset($this->configurations[$className])) {
            return $this->configurations[$className];
        }

        $this->configurations[$className] = [];
        $this->loadMetadataForObjectClass($metadata);

        return $this->configurations[$className];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): self
    {
        $metadata = $event->getClassMetadata();

        $this->classMetadata[$metadata->getName()] = $metadata;

        $this->loadMetadataForObjectClass($metadata);

        return $this;
    }

    /*
     * Gets the locale to use for translation. Loads object
     * defined locale first..
     */
    private function getTranslatableLocale(
        TranslatableInterface $object
    ): string {
        return $object->getLocaleField() ?? $this->locale;
    }

    /**
     * @param array<string, mixed> $config
     * @param ClassMetadata<ClassMetadataODM> $metaData
     */
    private function loadAllTranslations(
        WrapperInterface $wrapper,
        string $locale,
        string $translationClass,
        array $config,
        ClassMetadata $metaData
    ): void {
        $wrapper->loadAllTranslations(
            $this->persistence,
            $locale,
            $translationClass,
            $config['useObjectClass'],
            function (iterable $result) use ($wrapper, $config, $metaData) {
                if (empty($result)) {
                    return;
                }

                // translate object's translatable properties
                foreach ($config['fields'] as $field) {
                    $translated = '';
                    $isTranslated = false;
                    foreach ($result as $entry) {
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
                        $this->persistence->setTranslatedValue($wrapper, $metaData, $field, $translated);
                        $wrapper->setObjectPropertyInManager($this->manager, $field);
                    }
                }
            }
        );
    }

    /*
     * After object is loaded, listener updates the translations by currently used locale
     */
    public function postLoad(LifecycleEventArgs $event): self
    {
        $object = $event->getObject();

        if (!$object instanceof TranslatableInterface) {
            return $this;
        }

        $metaData = $this->getClassMetadata($this->getObjectClassName($object));

        $config = $this->getConfiguration($metaData);
        if (empty($config['fields'])) {
            return $this;
        }

        $locale = $this->getTranslatableLocale($object);
        $object->setLocaleField($locale);

        if ($locale === $this->defaultLocale) {
            return $this;
        }

        // fetch translations
        $translationClass = $config['translationClass'];
        $wrapper = $this->wrap($object, $metaData);

        $this->loadAllTranslations($wrapper, $locale, $translationClass, $config, $metaData);

        return $this;
    }

    /*
     * Creates and update the translation for object being flushed
     */
    private function handleTranslatableObjectChanges(
        TranslatableInterface $object,
        bool $isInsert
    ): void {
        $metaData = $this->getClassMetadata($this->getObjectClassName($object));
        $wrapper = $this->wrap($object, $metaData);
        $config = $this->getConfiguration($metaData);

        $translationClass = $config['translationClass'];

        // load the currently used locale
        $locale = $this->getTranslatableLocale($object);

        if ($locale === $this->defaultLocale) {
            return;
        }

        $this->objectsToTranslate[$locale][] = [$wrapper, $translationClass, $config, $metaData];

        $this->manager->ifObjectHasChangeSet(
            $object,
            function (array $changeSet) use (
                &$config,
                $object,
                &$locale,
                &$isInsert,
                $wrapper,
                &$translationClass,
                $metaData
            ) {
                // check for the availability of the primary key
                $oid = spl_object_hash($object);

                $translationMetadata = $this->getClassMetadata($translationClass);
                $translationReflection = $translationMetadata->getReflectionClass();

                $translatableFields = array_flip($config['fields']);
                foreach ($translatableFields as $field => $notUsed) {
                    if (!isset($changeSet[$field])) {
                        continue; // locale is same and nothing changed
                    }

                    $translation = null;
                    if (!$isInsert) {
                        $wrapper->findTranslation(
                            $this->persistence,
                            $locale,
                            $field,
                            $translationClass,
                            $config['useObjectClass'],
                            static function (TranslationInterface $result) use (&$translation) {
                                $translation = $result;
                            }
                        );
                    }

                    // create new translation if translation not already created and locale is different from default
                    // locale, otherwise, we have the date in the original record
                    if (!$translation instanceof TranslationInterface && $locale !== $this->defaultLocale) {
                        $translation = $translationReflection->newInstance();
                        if (!$translation instanceof TranslationInterface) {
                            throw new RuntimeException(
                                'Error the translation object does not implement the interface'
                            );
                        }

                        $translation->setLocale($locale);
                        $translation->setField($field);
                        $translation->setObjectClass($config['useObjectClass']);
                        $wrapper->linkTranslationRecord($translation);
                    }

                    if ($translation instanceof TranslationInterface) {
                        // set the translated field, take value using reflection
                        $this->persistence->updateTranslationRecord($wrapper, $metaData, $field, $translation);

                        if ($isInsert) {
                            // if we do not have the primary key yet available
                            // keep this translation in memory to insert it later with foreign key
                            $this->pendingTranslationInserts[$oid][] = $translation;
                        } else {
                            $this->persistence->persistTranslationRecord($translation);
                        }
                    }
                }

                // check if we have default translation and need to reset the translation
                if (!$isInsert) {
                    foreach ($changeSet as $field => $changes) {
                        $this->manager->setObjectPropertyInManager($oid, $field, $changes[0]);
                        if (isset($translatableFields[$field]) && $locale !== $this->defaultLocale) {
                            $wrapper->setPropertyValue($field, $changes[0]);
                        }
                    }

                    $this->manager->recomputeSingleObjectChangeset($metaData, $object);
                }
            }
        );
    }

    /*
     * Looks for translatable objects being inserted or updated for further processing
     */
    public function onFlush(): self
    {
        $this->objectsToTranslate = [];

        $handling = function ($object, $isInsert) {
            if (!$object instanceof TranslatableInterface) {
                return;
            }

            $metaData = $this->getClassMetadata($this->getObjectClassName($object));
            $config = $this->getConfiguration($metaData);

            if (isset($config['fields'])) {
                $this->handleTranslatableObjectChanges($object, $isInsert);
            }
        };

        // check all scheduled inserts for TranslatableInterface objects
        $this->manager->foreachScheduledObjectInsertions(static function ($object) use ($handling) {
            $handling($object, true);
        });

        $this->manager->foreachScheduledObjectUpdates(static function ($object) use ($handling) {
            $handling($object, false);
        });

        $this->manager->foreachScheduledObjectDeletions(function ($object) {
            if (!$object instanceof TranslatableInterface) {
                return;
            }

            $metaData = $this->getClassMetadata($this->getObjectClassName($object));
            $config = $this->getConfiguration($metaData);

            if (isset($config['fields'])) {
                $wrapper = $this->wrap($object, $metaData);
                $wrapper->removeAssociatedTranslations(
                    $this->persistence,
                    $config['translationClass'],
                    $config['useObjectClass']
                );
            }
        });

        return $this;
    }

    public function postFlush(): self
    {
        foreach ($this->objectsToTranslate as $local => &$objects) {
            foreach ($objects as &$object) {
                $this->loadAllTranslations($object[0], $local, $object[1], $object[2], $object[3]);
            }
            unset($object);
        }

        unset($objects);

        $this->objectsToTranslate = [];

        return $this;
    }

    /*
     * Checks for inserted object to update their translation foreign keys
     */
    public function postPersist(LifecycleEventArgs $event): self
    {
        $object = $event->getObject();

        if (!$object instanceof TranslatableInterface) {
            return $this;
        }

        $oid = spl_object_hash($object);

        if (!isset($this->pendingTranslationInserts[$oid])) {
            return $this;
        }

        $metaData = $this->getClassMetadata($this->getObjectClassName($object));
        $wrapper = $this->wrap($object, $metaData);
        // load the pending translations without key
        foreach ($this->pendingTranslationInserts[$oid] as $translation) {
            $wrapper->linkTranslationRecord($translation);
            $this->persistence->persistTranslationRecord($translation);
        }

        unset($this->pendingTranslationInserts[$oid]);

        return $this;
    }
}

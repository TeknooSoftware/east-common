<?php

/**
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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\GhostObjectInterface;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Translation;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface as PersistenceAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\FactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Object\TranslatableInterface;
use Teknoo\East\Website\Object\Type;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\TranslatableListener
 */
class TranslatableListenerTest extends TestCase
{
    private ?ExtensionMetadataFactory $extensionMetadataFactory = null;

    private ?ManagerAdapterInterface $manager = null;

    private ?PersistenceAdapterInterface $persistence = null;

    private ?FactoryInterface $wrapperFactory = null;

    /**
     * @return ExtensionMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getExtensionMetadataFactory(): ExtensionMetadataFactory
    {
        if (!$this->extensionMetadataFactory instanceof ExtensionMetadataFactory) {
            $this->extensionMetadataFactory = $this->createMock(ExtensionMetadataFactory::class);
        }

        return $this->extensionMetadataFactory;
    }

    /**
     * @return ManagerAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getManager(): ManagerAdapterInterface
    {
        if (!$this->manager instanceof ManagerAdapterInterface) {
            $this->manager = $this->createMock(ManagerAdapterInterface::class);
        }

        return $this->manager;
    }

    /**
     * @return PersistenceAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getPersistence(): PersistenceAdapterInterface
    {
        if (!$this->persistence instanceof PersistenceAdapterInterface) {
            $this->persistence = $this->createMock(PersistenceAdapterInterface::class);
        }

        return $this->persistence;
    }

    /**
     * @return FactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWrapperFactory(): FactoryInterface
    {
        if (!$this->wrapperFactory instanceof FactoryInterface) {
            $this->wrapperFactory = $this->createMock(FactoryInterface::class);
        }

        return $this->wrapperFactory;
    }

    public function build(string $locale = 'en', bool $fallback = true): TranslatableListener
    {
        return new TranslatableListener(
            $this->getExtensionMetadataFactory(),
            $this->getManager(),
            $this->getPersistence(),
            $this->getWrapperFactory(),
            $locale,
            'en',
            $fallback
        );
    }

    public function testGetSubscribedEvents()
    {
        self::assertIsArray(
            $this->build()->getSubscribedEvents()
        );
    }

    public function testRegisterClassMetadata()
    {
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->registerClassMetadata(
                'foo',
                $this->createMock(ClassMetadata::class)
            )
        );
    }

    public function testSetLocale()
    {
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale(
                'fr'
            )
        );
    }

    public function testSetLocaleEmpty()
    {
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale(
                ''
            )
        );
    }

    public function testInjectConfiguration()
    {
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->injectConfiguration(
                $this->createMock(ClassMetadata::class),
                ['fields' => ['foo', 'bar']]
            )
        );
    }

    public function testLoadClassMetadata()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn(Content::class);

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->expects(self::any())->method('getClassMetadata')->willReturn($classMeta);

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title'], 'fallback' => []]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->loadClassMetadata(
                $event
            )
        );
    }

    public function testPostLoadNonTranslatable()
    {
        $object = $this->createMock(Type::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $this->getManager()
            ->expects(self::never())
            ->method('findClassMetadata');

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postLoad(
                $event
            )
        );
    }

    public function testPostLoadWithNoTranslationConfig()
    {
        $object = $this->createMock(Content::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $classMeta = $this->createMock(ClassMetadata::class);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => [], 'fallback' => []]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::never())
            ->method('loadAllTranslations');

        $this->getWrapperFactory()
            ->expects(self::never())
            ->method('__invoke');

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postLoad(
                $event
            )
        );
    }

    public function testPostLoadErrorWithNoClassMetaData()
    {
        $object = $this->createMock(Content::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) {

                    return $this->getManager();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::never())
            ->method('loadAllTranslations');

        $this->getWrapperFactory()
            ->expects(self::never())
            ->method('__invoke');

        $this->expectException(\DomainException::class);
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postLoad(
                $event
            )
        );
    }

    public function testPostLoadWithDefaultLocale()
    {
        $object = $this->createMock(Content::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $classMeta = $this->createMock(ClassMetadata::class);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::never())
            ->method('loadAllTranslations');

        $this->getWrapperFactory()
            ->expects(self::never())
            ->method('__invoke');

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postLoad(
                $event
            )
        );
    }

    public function testPostLoadWithNoTranslationFound()
    {
        $object = $this->createMock(Content::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $classMeta = $this->createMock(ClassMetadata::class);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('loadAllTranslations')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    $callback([]);

                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale('fr')->postLoad(
                $event
            )
        );
    }

    public function testPostLoadWithTranslationFound()
    {
        $object = $this->createMock(Content::class);

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $classMeta = $this->createMock(ClassMetadata::class);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('loadAllTranslations')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    $callback([
                        ['field' => 'title', 'content' => 'foo'],
                        ['field' => 'subtitle', 'content' => 'bar'],
                    ]);

                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale('fr')->postLoad(
                $event
            )
        );
    }

    public function testPostLoadWithTranslationFoundForAProxy()
    {
        $object = new class extends Content implements GhostObjectInterface {
            public function setProxyInitializer(\Closure $initializer = null)
            {
            }

            public function getProxyInitializer(): \Closure
            {
            }

            public function initializeProxy(): bool
            {
            }

            public function isProxyInitialized(): bool
            {
            }
        };

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($object);

        $classMeta = $this->createMock(ClassMetadata::class);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('loadAllTranslations')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    $callback([
                        ['field' => 'title', 'content' => 'foo'],
                        ['field' => 'subtitle', 'content' => 'bar'],
                    ]);

                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale('fr')->postLoad(
                $event
            )
        );
    }

    public function testOnFlushOnDefaultLocale()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(Translation::class));

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::never())
            ->method('findTranslation');

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getManager()
            ->expects(self::never())
            ->method('ifObjectHasChangeSet');

        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectInsertions')
            ->willReturnCallback(function (callable $callback) {
                $callback(new Type());
                $callback(new Content());

                return $this->getManager();
            });

        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectUpdates')
            ->willReturnCallback(function (callable $callback) {
                $callback(new Type());
                $callback(new Content());

                return $this->getManager();
            });

        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectDeletions')
            ->willReturnCallback(function (callable $callback) {
                $callback(new Type());
                $callback(new Content());

                return $this->getManager();
            });

        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->setLocale('en')->onFlush()
        );
    }

    public function testOnFlushOnDifferentLocaleAndPostFlushAndPostPersist()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())
            ->method('getReflectionClass')
            ->willReturn(new \ReflectionClass(Translation::class));

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('findTranslation')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $field,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    $callback($this->createMock(Translation::class));

                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getManager()
            ->expects(self::any())
            ->method('ifObjectHasChangeSet')
            ->willReturnCallback(
                function ($object, callable $callback) {
                    $callback(['title' => ['foo', 'foo1']]);

                    return $this->getManager();
                }
            );

        $content = new Content();
        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectInsertions')
            ->willReturnCallback(function (callable $callback) use ($content) {
                $callback(new Type());
                $callback($content);

                return $this->getManager();
            });

        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectUpdates')
            ->willReturnCallback(function (callable $callback) {
                $callback(new Type());
                $callback(new Content());

                return $this->getManager();
            });

        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectDeletions')
            ->willReturnCallback(function (callable $callback) {
                $callback(new Type());
                $callback(new Content());

                return $this->getManager();
            });

        $listener = $this->build()->setLocale('fr');
        self::assertInstanceOf(
            TranslatableListener::class,
            $listener->onFlush()
        );

        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn($content);
        self::assertInstanceOf(
            TranslatableListener::class,
            $listener->postPersist($event)
        );

        self::assertInstanceOf(
            TranslatableListener::class,
            $listener->postFlush()
        );
    }

    public function testOnFlushErrorOnNewTranslationInstance()
    {
        $refClass = new class extends \ReflectionClass {
            public function __construct()
            {
                parent::__construct(Content::class);
            }

            public function newInstance(... $args): object
            {
                throw new \ReflectionException();
            }
        };

        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())
            ->method('getReflectionClass')
            ->willReturn($refClass);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('findTranslation')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $field,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getManager()
            ->expects(self::any())
            ->method('ifObjectHasChangeSet')
            ->willReturnCallback(
                function ($object, callable $callback) {
                    $callback(['title' => ['foo', 'foo1']]);

                    return $this->getManager();
                }
            );

        $content = new Content();
        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectInsertions')
            ->willReturnCallback(function (callable $callback) use ($content) {
                $callback(new Type());
                $callback($content);

                return $this->getManager();
            });

        $this->expectException(\RuntimeException::class);

        $listener = $this->build()->setLocale('fr');
        self::assertInstanceOf(
            TranslatableListener::class,
            $listener->onFlush()
        );
    }

    public function testOnFlushErrorOnNewTranslationInstanceNotGoodObject()
    {
        $refClass = new class extends \ReflectionClass {
            public function __construct()
            {
                parent::__construct(Content::class);
            }

            public function newInstance(... $args): object
            {
                return new \stdClass();
            }
        };

        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())
            ->method('getReflectionClass')
            ->willReturn($refClass);

        $this->getManager()
            ->expects(self::any())
            ->method('findClassMetadata')
            ->willReturnCallback(
                function (string $class, TranslatableListener $listener) use ($classMeta) {
                    $listener->registerClassMetadata(
                        $class,
                        $classMeta
                    );

                    return $this->getManager();
                }
            );

        $this->getExtensionMetadataFactory()
            ->expects(self::any())
            ->method('loadExtensionMetadata')
            ->willReturnCallback(
                function (ClassMetadata $metaData, TranslatableListener $listener) {
                    $listener->injectConfiguration(
                        $metaData,
                        ['fields' => ['title', 'subtitle'], 'fallback' => [], 'translationClass' => Translation::class, 'useObjectClass' => Content::class]
                    );

                    return $this->getExtensionMetadataFactory();
                }
            );

        $wrapper = $this->createMock(WrapperInterface::class);
        $wrapper->expects(self::any())
            ->method('findTranslation')
            ->willReturnCallback(
                function (
                    AdapterInterface $adapter,
                    string $locale,
                    string $field,
                    string $translationClass,
                    string $objectClass,
                    callable $callback
                ) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getWrapperFactory()
            ->expects(self::any())
            ->method('__invoke')
            ->willReturnCallback(
                function (TranslatableInterface $object, ClassMetadata $metadata) use ($wrapper) {
                    return $wrapper;
                }
            );

        $this->getManager()
            ->expects(self::any())
            ->method('ifObjectHasChangeSet')
            ->willReturnCallback(
                function ($object, callable $callback) {
                    $callback(['title' => ['foo', 'foo1']]);

                    return $this->getManager();
                }
            );

        $content = new Content();
        $this->getManager()
            ->expects(self::any())
            ->method('foreachScheduledObjectInsertions')
            ->willReturnCallback(function (callable $callback) use ($content) {
                $callback(new Type());
                $callback($content);

                return $this->getManager();
            });

        $this->expectException(\RuntimeException::class);

        $listener = $this->build()->setLocale('fr');
        self::assertInstanceOf(
            TranslatableListener::class,
            $listener->onFlush()
        );
    }

    public function testPostPersistNonTranslatable()
    {
        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn(new Type());
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postPersist($event)
        );
    }

    public function testPostPersistNonInserted()
    {
        $event = $this->createMock(LifecycleEventArgs::class);
        $event->expects(self::any())->method('getObject')->willReturn(new Content());
        self::assertInstanceOf(
            TranslatableListener::class,
            $this->build()->postPersist($event)
        );
    }
}

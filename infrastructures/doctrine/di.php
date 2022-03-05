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

namespace Teknoo\East\Website\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata as OdmClassMetadata;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use Exception;
use Psr\Container\ContainerInterface;
use ProxyManager\Proxy\GhostObjectInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use SimpleXMLElement;
use Teknoo\East\Website\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\SimpleXmlFactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\Xml;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverFactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\DriverInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\ExtensionMetadataFactory;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\Adapter\ODM as ODMAdapter;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\Adapter\ODM as ODMPersistence;
use Teknoo\East\Website\Object\TranslatableInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Doctrine\DBSource\Common\Manager;
use Teknoo\East\Website\Doctrine\DBSource\ODM\ContentRepository as OdmContentRepository;
use Teknoo\East\Website\Doctrine\DBSource\ODM\ItemRepository as OdmItemRepository;
use Teknoo\East\Website\Doctrine\DBSource\ODM\MediaRepository as OdmMediaRepository;
use Teknoo\East\Website\Doctrine\DBSource\ODM\TypeRepository as OdmTypeRepository;
use Teknoo\East\Website\Doctrine\DBSource\ODM\UserRepository as OdmUserRepository;
use Teknoo\East\Website\Doctrine\DBSource\Common\ContentRepository;
use Teknoo\East\Website\Doctrine\DBSource\Common\ItemRepository;
use Teknoo\East\Website\Doctrine\DBSource\Common\MediaRepository;
use Teknoo\East\Website\Doctrine\DBSource\Common\TypeRepository;
use Teknoo\East\Website\Doctrine\DBSource\Common\UserRepository;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\DocumentWrapper;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\FactoryInterface as WrapperFactory;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Writer\MediaWriter as OriginalWriter;
use Teknoo\East\Website\Service\ProxyDetectorInterface;
use Teknoo\East\Website\Doctrine\Writer\ODM\MediaWriter;

use function DI\get;

return [
    TranslatableListener::class => static function (ContainerInterface $container): TranslatableListener {
        $objectManager = $container->get(ObjectManager::class);
        $eastManager = $container->get(ManagerInterface::class);

        if (!$objectManager instanceof DocumentManager) {
            throw new RuntimeException('Sorry currently, this listener supports only ODM');
        }

        $eventManager = $objectManager->getEventManager();

        $translatableManagerAdapter = new ODMAdapter(
            $eastManager,
            $objectManager
        );

        $persistence = new ODMPersistence($objectManager);

        $mappingDriver = $objectManager->getConfiguration()->getMetadataDriverImpl();
        if (null === $mappingDriver) {
            throw new RuntimeException('The Mapping Driver is not available from the Doctrine manager');
        }

        $extensionMetadataFactory = new ExtensionMetadataFactory(
            $objectManager,
            $objectManager->getMetadataFactory(),
            $mappingDriver,
            new class implements DriverFactoryInterface {
                public function __invoke(FileLocator $locator): DriverInterface
                {
                    return new Xml(
                        $locator,
                        new class implements SimpleXmlFactoryInterface {
                            public function __invoke(string $file): SimpleXMLElement
                            {
                                return new SimpleXMLElement($file, 0, true);
                            }
                        }
                    );
                }
            }
        );

        $translatableListener = new TranslatableListener(
            $extensionMetadataFactory,
            $translatableManagerAdapter,
            $persistence,
            new class implements WrapperFactory {
                public function __invoke(TranslatableInterface $object, ClassMetadata $metadata): WrapperInterface
                {
                    if (!$metadata instanceof OdmClassMetadata) {
                        throw new RuntimeException('Error wrapper support only ' . OdmClassMetadata::class);
                    }

                    return new DocumentWrapper($object, $metadata);
                }
            }
        );

        $eventManager->addEventSubscriber($translatableListener);

        return $translatableListener;
    },

    ManagerInterface::class => get(Manager::class),
    Manager::class => static function (ContainerInterface $container): Manager {
        $objectManager = $container->get(ObjectManager::class);
        return new Manager($objectManager);
    },

    ContentRepositoryInterface::class => static function (ContainerInterface $container): ContentRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Content::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmContentRepository($repository);
        }

        $repository = $container->get(ObjectManager::class)->getRepository(Content::class);
        if ($repository instanceof ObjectRepository) {
            return new ContentRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    ItemRepositoryInterface::class => static function (ContainerInterface $container): ItemRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Item::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmItemRepository($repository);
        }

        if ($repository instanceof ObjectRepository) {
            return new ItemRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    MediaRepositoryInterface::class => static function (ContainerInterface $container): MediaRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmMediaRepository($repository);
        }

        if ($repository instanceof ObjectRepository) {
            return new MediaRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    TypeRepositoryInterface::class => static function (ContainerInterface $container): TypeRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Type::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmTypeRepository($repository);
        }

        if ($repository instanceof ObjectRepository) {
            return new TypeRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    UserRepositoryInterface::class => static function (ContainerInterface $container): UserRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(User::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmUserRepository($repository);
        }

        if ($repository instanceof ObjectRepository) {
            return new UserRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    LocaleMiddleware::class => static function (ContainerInterface $container): LocaleMiddleware {
        if (
            $container->has(ObjectManager::class)
            && ($container->get(ObjectManager::class)) instanceof DocumentManager
        ) {
            $listener = $container->get(TranslatableListener::class);
            $callback = $listener->setLocale(...);
        } else {
            //do nothing
            $callback = null;
        }

        return new LocaleMiddleware($callback);
    },

    MediaWriter::class => static function (ContainerInterface $container): MediaWriter {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof GridFSRepository) {
            return new MediaWriter($repository, $container->get(OriginalWriter::class));
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },
    'teknoo.east.website.doctrine.writer.media.new' => get(MediaWriter::class),

    ProxyDetectorInterface::class => static function (): ProxyDetectorInterface {
        return new class implements ProxyDetectorInterface {
            public function checkIfInstanceBehindProxy(
                object $object,
                PromiseInterface $promise
            ): ProxyDetectorInterface {
                if (!$object instanceof GhostObjectInterface) {
                    $promise->fail(new Exception('Object is not behind a proxy'));

                    return $this;
                }

                if ($object->isProxyInitialized()) {
                    $promise->fail(new Exception('Proxy is already initialized'));

                    return $this;
                }

                $promise->success($object);

                return $this;
            }
        };
    },

    GetStreamFromMediaInterface::class => get(GetStreamFromMedia::class),
    GetStreamFromMedia::class => static function (ContainerInterface $container): GetStreamFromMedia {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if (!$repository instanceof GridFSRepository) {
            throw new RuntimeException('Repository for Media class is not a GridFSRepository');
        }

        return new GetStreamFromMedia(
            $repository,
            $container->get(StreamFactoryInterface::class)
        );
    },
];

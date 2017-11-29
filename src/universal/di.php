<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website;

use function DI\get;
use function DI\decorate;
use function DI\object;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Gedmo\Translatable\TranslatableListener;
use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\MongoDbCollectionLoaderTrait;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Object\Item;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;

return [
    //Loaders
    ItemLoader::class => function (ContainerInterface $container) {
        $repository = $container->get(ObjectManager::class)->getRepository(Item::class);
        if ($repository instanceof DocumentRepository) {
            return new class($repository) extends ItemLoader {
                use MongoDbCollectionLoaderTrait;
            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },
    ContentLoader::class => function (ContainerInterface $container) {
        $repository = $container->get(ObjectManager::class)->getRepository(Content::class);
        if ($repository instanceof DocumentRepository) {
            return new class($repository) extends ContentLoader {
                use MongoDbCollectionLoaderTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },
    MediaLoader::class => function (ContainerInterface $container) {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof DocumentRepository) {
            return new class($repository) extends MediaLoader {
                use MongoDbCollectionLoaderTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },
    TypeLoader::class => function (ContainerInterface $container) {
        $repository = $container->get(ObjectManager::class)->getRepository(Type::class);
        if ($repository instanceof DocumentRepository) {
            return new class($repository) extends TypeLoader {
                use MongoDbCollectionLoaderTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },
    UserLoader::class => function (ContainerInterface $container) {
        $repository = $container->get(ObjectManager::class)->getRepository(User::class);
        if ($repository instanceof DocumentRepository) {
            return new class($repository) extends UserLoader {
                use MongoDbCollectionLoaderTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    //Writer
    ItemWriter::class => object(ItemWriter::class)
        ->constructor(get(ObjectManager::class)),
    ContentWriter::class => object(ContentWriter::class)
        ->constructor(get(ObjectManager::class)),
    MediaWriter::class => object(MediaWriter::class)
        ->constructor(get(ObjectManager::class)),
    TypeWriter::class => object(TypeWriter::class)
        ->constructor(get(ObjectManager::class)),
    UserWriter::class => object(UserWriter::class)
        ->constructor(get(ObjectManager::class)),

    //Deleting
    'teknoo.east.website.deleting.item' => object(DeletingService::class)
        ->constructor(get(ItemWriter::class)),
    'teknoo.east.website.deleting.content' => object(DeletingService::class)
        ->constructor(get(ContentWriter::class)),
    'teknoo.east.website.deleting.media' => object(DeletingService::class)
        ->constructor(get(MediaWriter::class)),
    'teknoo.east.website.deleting.type' => object(DeletingService::class)
        ->constructor(get(TypeWriter::class)),
    'teknoo.east.website.deleting.user' => object(DeletingService::class)
        ->constructor(get(UserWriter::class)),

    //Menu
    MenuGenerator::class => object(MenuGenerator::class)
        ->constructor(get(ItemLoader::class)),
    MenuMiddleware::class => object(MenuMiddleware::class)
        ->constructor(get(MenuGenerator::class)),

    //Middleware
    LocaleMiddleware::class => function (ContainerInterface $container): LocaleMiddleware {
        $listener = null;
        if ($container->has('stof_doctrine_extensions.listener.translatable')) {
            $listener = $container->get('stof_doctrine_extensions.listener.translatable');
        } else {
            $listener = $container->get(TranslatableListener::class);
        }

        return new LocaleMiddleware($listener);
    },

    RecipeInterface::class => decorate(function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->registerMiddleware(
                $container->get(LocaleMiddleware::class),
                LocaleMiddleware::MIDDLEWARE_PRIORITY
            );
            $previous = $previous->registerMiddleware(
                $container->get(MenuMiddleware::class),
                MenuMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),
];

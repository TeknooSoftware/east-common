<?php

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
use Gedmo\Translatable\TranslatableListener;
use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Loader\CategoryLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Object\Category;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\CategoryWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;

return [
    //Loaders
    CategoryLoader::class => function (ContainerInterface $container) {
        return new CategoryLoader($container->get(ObjectManager::class)->getRepository(Category::class));
    },
    ContentLoader::class => function (ContainerInterface $container) {
        return new ContentLoader($container->get(ObjectManager::class)->getRepository(Content::class));
    },
    MediaLoader::class => function (ContainerInterface $container) {
        return new MediaLoader($container->get(ObjectManager::class)->getRepository(Media::class));
    },
    TypeLoader::class => function (ContainerInterface $container) {
        return new TypeLoader($container->get(ObjectManager::class)->getRepository(Type::class));
    },
    UserLoader::class => function (ContainerInterface $container) {
        return new UserLoader($container->get(ObjectManager::class)->getRepository(User::class));
    },

    //Writer
    CategoryWriter::class => object(CategoryWriter::class)
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
    'teknoo.east.website.deleting.category' => object(DeletingService::class)
        ->constructor(get(CategoryWriter::class)),
    'teknoo.east.website.deleting.content' => object(DeletingService::class)
        ->constructor(get(ContentWriter::class)),
    'teknoo.east.website.deleting.media' => object(DeletingService::class)
        ->constructor(get(MediaWriter::class)),
    'teknoo.east.website.deleting.type' => object(DeletingService::class)
        ->constructor(get(TypeWriter::class)),
    'teknoo.east.website.deleting.user' => object(DeletingService::class)
        ->constructor(get(UserWriter::class)),

    MenuGenerator::class => object(MenuGenerator::class)
        ->constructor(get(CategoryLoader::class)),

    //Middleware
    LocaleMiddleware::class => object(LocaleMiddleware::class)
        ->constructor(get(TranslatableListener::class)),

    ManagerInterface::class => decorate(function ($previous, ContainerInterface $container) {
        if ($previous instanceof ManagerInterface) {
            $previous->registerMiddleware($container->get(LocaleMiddleware::class), LocaleMiddleware::MIDDLEWARE_PRIORITY);
        }

        return $previous;
    }),
];

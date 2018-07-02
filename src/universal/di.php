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
use function DI\create;
use Gedmo\Translatable\TranslatableListener;
use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;

return [
    //Loaders
    ItemLoader::class => create(ItemLoader::class)
        ->constructor(get(ItemRepositoryInterface::class)),
    ContentLoader::class => create(ContentLoader::class)
        ->constructor(get(ContentRepositoryInterface::class)),
    MediaLoader::class => create(MediaLoader::class)
        ->constructor(get(MediaRepositoryInterface::class)),
    TypeLoader::class => create(TypeLoader::class)
        ->constructor(get(TypeRepositoryInterface::class)),
    UserLoader::class => create(UserLoader::class)
        ->constructor(get(UserRepositoryInterface::class)),

    //Writer
    ItemWriter::class => create(ItemWriter::class)
        ->constructor(get(ManagerInterface::class)),
    ContentWriter::class => create(ContentWriter::class)
        ->constructor(get(ManagerInterface::class)),
    MediaWriter::class => create(MediaWriter::class)
        ->constructor(get(ManagerInterface::class)),
    TypeWriter::class => create(TypeWriter::class)
        ->constructor(get(ManagerInterface::class)),
    UserWriter::class => create(UserWriter::class)
        ->constructor(get(ManagerInterface::class)),

    //Deleting
    'teknoo.east.website.deleting.item' => create(DeletingService::class)
        ->constructor(get(ItemWriter::class)),
    'teknoo.east.website.deleting.content' => create(DeletingService::class)
        ->constructor(get(ContentWriter::class)),
    'teknoo.east.website.deleting.media' => create(DeletingService::class)
        ->constructor(get(MediaWriter::class)),
    'teknoo.east.website.deleting.type' => create(DeletingService::class)
        ->constructor(get(TypeWriter::class)),
    'teknoo.east.website.deleting.user' => create(DeletingService::class)
        ->constructor(get(UserWriter::class)),

    //Menu
    MenuGenerator::class => create(MenuGenerator::class)
        ->constructor(get(ItemLoader::class)),
    MenuMiddleware::class => create(MenuMiddleware::class)
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

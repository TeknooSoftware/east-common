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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\CreateContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\DeleteContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\EditContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\ListContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderDynamicContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderMediaEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderStaticContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Website\Recipe\Cookbook\DeleteContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\EditContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\ListContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderDynamicContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderStaticContentEndPoint;
use Teknoo\East\Website\Recipe\Step\CreateObject;
use Teknoo\East\Website\Recipe\Step\InitParametersBag;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
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
use Teknoo\East\Website\Recipe\Cookbook\CreateContentEndPoint;
use Teknoo\East\Website\Recipe\Step\DeleteObject;
use Teknoo\East\Website\Recipe\Step\ExtractOrder;
use Teknoo\East\Website\Recipe\Step\ExtractPage;
use Teknoo\East\Website\Recipe\Step\ExtractSlug;
use Teknoo\East\Website\Recipe\Step\LoadContent;
use Teknoo\East\Website\Recipe\Step\LoadListObjects;
use Teknoo\East\Website\Recipe\Step\LoadMedia;
use Teknoo\East\Website\Recipe\Step\LoadObject;
use Teknoo\East\Website\Recipe\Step\Render;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\RenderList;
use Teknoo\East\Website\Recipe\Step\SaveObject;
use Teknoo\East\Website\Recipe\Step\SendMedia;
use Teknoo\East\Website\Recipe\Step\SlugPreparation;
use Teknoo\East\Website\Service\DatesService;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\FindSlugService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Service\ProxyDetectorInterface;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\Recipe\Recipe;

use function DI\get;
use function DI\decorate;
use function DI\create;

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
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    ContentWriter::class => create(ContentWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    MediaWriter::class => create(MediaWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    TypeWriter::class => create(TypeWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),
    UserWriter::class => create(UserWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),

    //Deleting
    'teknoo.east.website.deleting.item' => create(DeletingService::class)
        ->constructor(get(ItemWriter::class), get(DatesService::class)),
    'teknoo.east.website.deleting.content' => create(DeletingService::class)
        ->constructor(get(ContentWriter::class), get(DatesService::class)),
    'teknoo.east.website.deleting.media' => create(DeletingService::class)
        ->constructor(get(MediaWriter::class), get(DatesService::class)),
    'teknoo.east.website.deleting.type' => create(DeletingService::class)
        ->constructor(get(TypeWriter::class), get(DatesService::class)),
    'teknoo.east.website.deleting.user' => create(DeletingService::class)
        ->constructor(get(UserWriter::class), get(DatesService::class)),

    //Menu
    MenuGenerator::class => static function (ContainerInterface $container): MenuGenerator {
        return new MenuGenerator(
            $container->get(ItemLoader::class),
            $container->get(ContentLoader::class),
            $container->has(ProxyDetectorInterface::class) ? $container->get(ProxyDetectorInterface::class) : null
        );
    },

    MenuMiddleware::class => create(MenuMiddleware::class)
        ->constructor(get(MenuGenerator::class)),

    //Service
    FindSlugService::class => create(FindSlugService::class),
    DatesService::class => create(DatesService::class),

    //Middleware
    RecipeInterface::class => decorate(static function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->cook(
                $container->get(InitParametersBag::class),
                InitParametersBag::class,
                [],
                4
            );

            if ($container->has(LocaleMiddleware::class)) {
                $previous = $previous->cook(
                    [$container->get(LocaleMiddleware::class), 'execute'],
                    LocaleMiddleware::class,
                    [],
                    LocaleMiddleware::MIDDLEWARE_PRIORITY
                );
            }

            $previous = $previous->cook(
                [$container->get(MenuMiddleware::class), 'execute'],
                MenuMiddleware::class,
                [],
                MenuMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),

    //Steps
    CreateObject::class => create(),
    DeleteObject::class => create()
        ->constructor(
            get(DeletingService::class)
        ),
    ExtractOrder::class => create(),
    ExtractPage::class => create(),
    ExtractSlug::class => create(),
    InitParametersBag::class => create(),
    LoadContent::class => create()
        ->constructor(
            get(ContentLoader::class)
        ),
    LoadListObjects::class => create(),
    LoadMedia::class => create()
        ->constructor(
            get(MediaLoader::class)
        ),
    LoadObject::class => create(),
    Render::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
    RenderError::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
    RenderList::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
    SaveObject::class => create(),
    SendMedia::class => create()
        ->constructor(
            get(ResponseFactoryInterface::class)
        ),
    SlugPreparation::class => create()
        ->constructor(
            get(FindSlugService::class)
        ),

    //Base recipe
    OriginalRecipeInterface::class => get(Recipe::class),
    Recipe::class => create(),

    //Cookbook
    CreateContentEndPointInterface::class => get(CreateContentEndPoint::class),
    CreateContentEndPoint::class => static function (ContainerInterface $container): CreateContentEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        return new CreateContentEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl
        );
    },
    DeleteContentEndPointInterface::class => get(DeleteContentEndPoint::class),
    DeleteContentEndPoint::class => static function (ContainerInterface $container): DeleteContentEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        return new DeleteContentEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(LoadObject::class),
            $container->get(DeleteObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderError::class),
            $accessControl
        );
    },
    EditContentEndPointInterface::class => get(EditContentEndPoint::class),
    EditContentEndPoint::class => static function (ContainerInterface $container): EditContentEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        return new EditContentEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(LoadObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl
        );
    },
    ListContentEndPointInterface::class => get(ListContentEndPoint::class),
    ListContentEndPoint::class => static function (ContainerInterface $container): ListContentEndPoint {
        $formLoader = null;
        if ($container->has(SearchFormLoaderInterface::class)) {
            $formLoader = $container->get(SearchFormLoaderInterface::class);
        }

        $accessControl = null;
        if ($container->has(ListObjectsAccessControlInterface::class)) {
            $accessControl = $container->get(ListObjectsAccessControlInterface::class);
        }

        return new ListContentEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(ExtractPage::class),
            $container->get(ExtractOrder::class),
            $container->get(LoadListObjects::class),
            $container->get(RenderList::class),
            $container->get(RenderError::class),
            $formLoader,
            $accessControl
        );
    },
    RenderDynamicContentEndPointInterface::class => get(RenderDynamicContentEndPoint::class),
    RenderDynamicContentEndPoint::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(ExtractSlug::class),
            get(LoadContent::class),
            get(Render::class),
            get(RenderError::class)
        ),
    RenderMediaEndPointInterface::class => get(RenderMediaEndPoint::class),
    RenderMediaEndPoint::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadMedia::class),
            get(GetStreamFromMediaInterface::class),
            get(SendMedia::class),
            get(RenderError::class)
        ),
    RenderStaticContentEndPointInterface::class => get(RenderStaticContentEndPoint::class),
    RenderStaticContentEndPoint::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(Render::class),
            get(RenderError::class)
        ),
];

<?php

/**
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\CreateObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\EditObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\ListObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Recipe\Cookbook\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\DeleteObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\EditObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\ExtractSlug;
use Teknoo\East\Common\Recipe\Step\InitParametersBag;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\RenderStaticContentEndPointInterface;
use Teknoo\East\Common\Recipe\Cookbook\RenderStaticContentEndPoint;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

use function DI\create;
use function DI\decorate;
use function DI\get;

return [
    //Loaders
    UserLoader::class => create(UserLoader::class)
        ->constructor(get(UserRepositoryInterface::class)),

    //Writer
    UserWriter::class => create(UserWriter::class)
        ->constructor(get(ManagerInterface::class), get(DatesService::class)),

    //Deleting
    'teknoo.east.common.deleting.user' => create(DeletingService::class)
        ->constructor(get(UserWriter::class), get(DatesService::class)),

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
    LoadListObjects::class => create(),
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
    SlugPreparation::class => create()
        ->constructor(
            get(FindSlugService::class)
        ),

    //Base recipe
    OriginalRecipeInterface::class => get(Recipe::class),
    Recipe::class => create(),

    //Cookbook
    CreateObjectEndPointInterface::class => get(CreateObjectEndPoint::class),
    CreateObjectEndPoint::class => static function (ContainerInterface $container): CreateObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorMapping = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorMapping = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new CreateObjectEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorMapping,
        );
    },
    DeleteObjectEndPointInterface::class => get(DeleteObjectEndPoint::class),
    DeleteObjectEndPoint::class => static function (ContainerInterface $container): DeleteObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorMapping = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorMapping = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new DeleteObjectEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(LoadObject::class),
            $container->get(DeleteObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorMapping,
        );
    },
    EditObjectEndPointInterface::class => get(EditObjectEndPoint::class),
    EditObjectEndPoint::class => static function (ContainerInterface $container): EditObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorMapping = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorMapping = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new EditObjectEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(LoadObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorMapping,
        );
    },
    ListObjectEndPointInterface::class => get(ListObjectEndPoint::class),
    ListObjectEndPoint::class => static function (ContainerInterface $container): ListObjectEndPoint {
        $formLoader = null;
        if ($container->has(SearchFormLoaderInterface::class)) {
            $formLoader = $container->get(SearchFormLoaderInterface::class);
        }

        $accessControl = null;
        if ($container->has(ListObjectsAccessControlInterface::class)) {
            $accessControl = $container->get(ListObjectsAccessControlInterface::class);
        }

        $defaultErrorMapping = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorMapping = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new ListObjectEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(ExtractPage::class),
            $container->get(ExtractOrder::class),
            $container->get(LoadListObjects::class),
            $container->get(RenderList::class),
            $container->get(RenderError::class),
            $formLoader,
            $accessControl,
            $defaultErrorMapping,
        );
    },
    RenderStaticContentEndPointInterface::class => get(RenderStaticContentEndPoint::class),
    RenderStaticContentEndPoint::class => static function (ContainerInterface $container): RenderStaticContentEndPoint {
        $defaultErrorMapping = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorMapping = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new RenderStaticContentEndPoint(
            $container->get(OriginalRecipeInterface::class),
            $container->get(Render::class),
            $container->get(RenderError::class),
            $defaultErrorMapping,
        );
    },
];

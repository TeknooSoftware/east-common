<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\CreateObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\EditObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\ListObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierCommandInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\PrepareRecoveryAccessEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\RenderMediaEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\RenderStaticContentEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\NotifyUserAboutRecoveryAccessInterface;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Middleware\LocaleMiddleware;
use Teknoo\East\Common\Recipe\Cookbook\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\DeleteObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\EditObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\MinifierCommand;
use Teknoo\East\Common\Recipe\Cookbook\MinifierEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\PrepareRecoveryAccessEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\RenderStaticContentEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\ExtractSlug;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ComputePath;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadPersistedAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ReturnFile;
use Teknoo\East\Common\Recipe\Step\InitParametersBag;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadMedia;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SendMedia;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Common\Recipe\Step\User\FindUserByEmail;
use Teknoo\East\Common\Recipe\Step\User\PrepareRecoveryAccess;
use Teknoo\East\Common\Recipe\Step\User\RemoveRecoveryAccess;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken;
use Teknoo\East\Common\Writer\MediaWriter;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Time\DatesService as CommonDatesService;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

use function DI\create;
use function DI\decorate;
use function DI\get;
use function DI\value;

return [
    //Loaders
    UserLoader::class => create(UserLoader::class)
        ->constructor(get(UserRepositoryInterface::class)),
    MediaLoader::class => create(MediaLoader::class)
        ->constructor(get(MediaRepositoryInterface::class)),

    //Writer
    UserWriter::class => create(UserWriter::class)
        ->constructor(get(ManagerInterface::class), get(CommonDatesService::class)),
    MediaWriter::class => create(MediaWriter::class)
        ->constructor(get(ManagerInterface::class), get(CommonDatesService::class)),

    //Deleting
    'teknoo.east.common.deleting.user' => create(DeletingService::class)
        ->constructor(get(UserWriter::class), get(CommonDatesService::class)),
    'teknoo.east.common.deleting.media' => create(DeletingService::class)
        ->constructor(get(MediaWriter::class), get(CommonDatesService::class)),

    //Service
    FindSlugService::class => create(FindSlugService::class),
    DatesService::class => create(DatesService::class),

    //User
    'teknoo.east.common.user.recovery.time_limited.delay' => '1 hour',
    TimeLimitedToken::class => create(TimeLimitedToken::class)
        ->constructor(
            get(CommonDatesService::class),
            get('teknoo.east.common.user.recovery.time_limited.delay'),
        ),

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

        if ($container->has(LocaleMiddleware::class)) {
            $previous = $previous->cook(
                [$container->get(LocaleMiddleware::class), 'execute'],
                LocaleMiddleware::class,
                [],
                LocaleMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),

    'teknoo.east.common.rendering.clean_html' => value(false),

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
    JumpIf::class => create(),
    JumpIfNot::class => create(),
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
            get(ResponseFactoryInterface::class),
            get('teknoo.east.common.rendering.clean_html'),
        ),
    RenderError::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class),
            get('teknoo.east.common.rendering.clean_html'),
        ),
    RenderList::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class),
            get('teknoo.east.common.rendering.clean_html'),
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
    Stop::class => create(),
    ComputePath::class => static function (ContainerInterface $container): ComputePath {
        $finalAssetsLocation = '';
        if ($container->has('teknoo.east.common.assets.final_location')) {
            $finalAssetsLocation = $container->get('teknoo.east.common.assets.final_location');
        }

        return new ComputePath(
            finalAssetsLocation: $finalAssetsLocation,
        );
    },
    LoadPersistedAsset::class => create(),
    LoadSource::class => create(),
    MinifyAssets::class => create(),
    PersistAsset::class => create(),
    ReturnFile::class => create()
        ->constructor(
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class),
        ),
    FindUserByEmail::class => create(),
    PrepareRecoveryAccess::class => create(),
    RemoveRecoveryAccess::class => create(),

    //Base recipe
    OriginalRecipeInterface::class . ':CRUD' => get(OriginalRecipeInterface::class),
    OriginalRecipeInterface::class . ':Static' => get(OriginalRecipeInterface::class),
    OriginalRecipeInterface::class . ':Asset' => get(OriginalRecipeInterface::class),
    OriginalRecipeInterface::class . ':Auth' => get(OriginalRecipeInterface::class),
    OriginalRecipeInterface::class => get(Recipe::class),
    Recipe::class => create(),

    //Cookbook
    CreateObjectEndPointInterface::class => get(CreateObjectEndPoint::class),
    CreateObjectEndPoint::class => static function (ContainerInterface $container): CreateObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new CreateObjectEndPoint(
            $container->get(OriginalRecipeInterface::class . ':CRUD'),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RedirectClientInterface::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorTemplate,
        );
    },
    DeleteObjectEndPointInterface::class => get(DeleteObjectEndPoint::class),
    DeleteObjectEndPoint::class => static function (ContainerInterface $container): DeleteObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new DeleteObjectEndPoint(
            $container->get(OriginalRecipeInterface::class . ':CRUD'),
            $container->get(LoadObject::class),
            $container->get(DeleteObject::class),
            $container->get(JumpIf::class),
            $container->get(RedirectClientInterface::class),
            $container->get(Render::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorTemplate,
        );
    },
    EditObjectEndPointInterface::class => get(EditObjectEndPoint::class),
    EditObjectEndPoint::class => static function (ContainerInterface $container): EditObjectEndPoint {
        $accessControl = null;
        if ($container->has(ObjectAccessControlInterface::class)) {
            $accessControl = $container->get(ObjectAccessControlInterface::class);
        }

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new EditObjectEndPoint(
            $container->get(OriginalRecipeInterface::class . ':CRUD'),
            $container->get(LoadObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(SlugPreparation::class),
            $container->get(SaveObject::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $accessControl,
            $defaultErrorTemplate,
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

        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new ListObjectEndPoint(
            $container->get(OriginalRecipeInterface::class . ':CRUD'),
            $container->get(ExtractPage::class),
            $container->get(ExtractOrder::class),
            $container->get(LoadListObjects::class),
            $container->get(RenderList::class),
            $container->get(RenderError::class),
            $formLoader,
            $accessControl,
            $defaultErrorTemplate,
        );
    },
    MinifierCommandInterface::class => get(MinifierCommand::class),
    MinifierCommand::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class . ':CRUD'),
            get(LoadSource::class),
            get(MinifyAssets::class),
            get(PersistAsset::class),
        ),
    MinifierEndPointInterface::class => get(MinifierEndPoint::class),
    MinifierEndPoint::class => static function (ContainerInterface $container): MinifierEndPoint {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new MinifierEndPoint(
            $container->get(OriginalRecipeInterface::class . ':Asset'),
            $container->get(ComputePath::class),
            $container->get(LoadPersistedAsset::class),
            $container->get(JumpIf::class),
            $container->get(LoadSource::class),
            $container->get(MinifyAssets::class),
            $container->get(PersistAsset::class),
            $container->get(ReturnFile::class),
            $container->get(RenderError::class),
            $defaultErrorTemplate,
        );
    },

    PrepareRecoveryAccessEndPointInterface::class => get(PrepareRecoveryAccessEndPoint::class),
    PrepareRecoveryAccessEndPoint::class => static function (
        ContainerInterface $container
    ): PrepareRecoveryAccessEndPoint {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new PrepareRecoveryAccessEndPoint(
            $container->get(OriginalRecipeInterface::class . ':Auth'),
            $container->get(CreateObject::class),
            $container->get(FormHandlingInterface::class),
            $container->get(FormProcessingInterface::class),
            $container->get(FindUserByEmail::class),
            $container->get(JumpIfNot::class),
            $container->get(RemoveRecoveryAccess::class),
            $container->get(PrepareRecoveryAccess::class),
            $container->get(SaveObject::class),
            $container->get(NotifyUserAboutRecoveryAccessInterface::class),
            $container->get(Render::class),
            $container->get(Stop::class),
            $container->get(RenderFormInterface::class),
            $container->get(RenderError::class),
            $defaultErrorTemplate,
        );
    },

    RenderStaticContentEndPointInterface::class => get(RenderStaticContentEndPoint::class),
    RenderStaticContentEndPoint::class => static function (ContainerInterface $container): RenderStaticContentEndPoint {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new RenderStaticContentEndPoint(
            $container->get(OriginalRecipeInterface::class . ':Static'),
            $container->get(Render::class),
            $container->get(RenderError::class),
            $defaultErrorTemplate,
        );
    },

    RenderMediaEndPointInterface::class => get(RenderMediaEndPoint::class),
    RenderMediaEndPoint::class => create()
        ->constructor(
            get(OriginalRecipeInterface::class),
            get(LoadMedia::class),
            get(GetStreamFromMediaInterface::class),
            get(SendMedia::class),
            get(RenderError::class)
        ),
];

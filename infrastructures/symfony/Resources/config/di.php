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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Disable2FA;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Enable2FA;
use Teknoo\East\CommonBundle\Recipe\Cookbook\GenerateQRCode;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Validate2FA;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\EnableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\FormHandling;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\RedirectClient;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;
use Teknoo\East\CommonBundle\Recipe\Step\SearchFormLoader;
use Teknoo\East\CommonBundle\Recipe\Step\ValidateTOTP;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

use function DI\create;
use function DI\decorate;
use function DI\get;

return [
    //Middleware
    LocaleMiddleware::class => static function (ContainerInterface $container): LocaleMiddleware {
        return new LocaleMiddleware($container->get('translator'));
    },

    RecipeInterface::class => decorate(static function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->cook(
                [$container->get(LocaleMiddleware::class), 'execute'],
                LocaleMiddleware::class,
                [],
                LocaleMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),

    //Search
    SearchFormLoaderInterface::class => get(SearchFormLoader::class),

    //Form
    FormHandlingInterface::class => get(FormHandling::class),

    FormProcessingInterface::class => get(FormProcessing::class),
    FormProcessing::class => create(),

    //Redirect
    RedirectClientInterface::class => get(RedirectClient::class),

    //Rendering
    RenderFormInterface::class => get(RenderForm::class),
    RenderForm::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),

    //TOTP Cookbook
    Enable2FA::class => static function (ContainerInterface $container): Enable2FA {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new Enable2FA(
            recipe: $container->get(OriginalRecipeInterface::class),
            enableTOTP: $container->get(EnableTOTP::class),
            createObject: $container->get(CreateObject::class),
            formHandling: $container->get(FormHandlingInterface::class),
            renderForm: $container->get(RenderFormInterface::class),
            renderError: $container->get(RenderError::class),
            defaultErrorTemplate: $defaultErrorTemplate,
        );
    },
    Disable2FA::class => static function (ContainerInterface $container): Disable2FA {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new Disable2FA(
            recipe: $container->get(OriginalRecipeInterface::class),
            disableTOTP: $container->get(DisableTOTP::class),
            redirectClient: $container->get(RedirectClientInterface::class),
            renderError: $container->get(RenderError::class),
            defaultErrorTemplate: $defaultErrorTemplate,
        );
    },
    GenerateQRCode::class => static function (ContainerInterface $container): GenerateQRCode {
        return new GenerateQRCode(
            $container->get(OriginalRecipeInterface::class),
            $container->get(GenerateQRCodeTextForTOTP::class),
            $container->get(BuildQrCodeInterface::class),
            $container->get(RenderError::class),
        );
    },
    Validate2FA::class => static function (ContainerInterface $container): Validate2FA {
        $defaultErrorTemplate = null;
        if ($container->has('teknoo.east.common.cookbook.default_error_template')) {
            $defaultErrorTemplate = $container->get('teknoo.east.common.cookbook.default_error_template');
        }

        return new Validate2FA(
            recipe: $container->get(OriginalRecipeInterface::class),
            createObject: $container->get(CreateObject::class),
            formHandling: $container->get(FormHandlingInterface::class),
            formProcessing: $container->get(FormProcessingInterface::class),
            validateTOTP: $container->get(ValidateTOTP::class),
            redirectClient: $container->get(RedirectClientInterface::class),
            renderForm: $container->get(RenderFormInterface::class),
            renderError: $container->get(RenderError::class),
            defaultErrorTemplate: $defaultErrorTemplate,
        );
    },
];

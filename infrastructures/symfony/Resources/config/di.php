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

namespace Teknoo\East\CommonBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\CommonBundle\Command\Exception\MissingMessageFactoryException;
use Teknoo\East\CommonBundle\Command\MinifyCommand;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;
use Teknoo\East\CommonBundle\Recipe\Plan\Disable2FA;
use Teknoo\East\CommonBundle\Recipe\Plan\Enable2FA;
use Teknoo\East\CommonBundle\Recipe\Plan\GenerateQRCode;
use Teknoo\East\CommonBundle\Recipe\Plan\Validate2FA;
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
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Foundation\Recipe\PlanInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\FoundationBundle\Command\Client;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

use function DI\create;
use function DI\decorate;
use function DI\get;
use function DI\value;

return [
    //Middleware
    LocaleMiddleware::class => static function (ContainerInterface $container): LocaleMiddleware {
        return new LocaleMiddleware($container->get('translator'));
    },

    PlanInterface::class => decorate(static function (PlanInterface $previous, ContainerInterface $container) {
        /** @var LocaleMiddleware $localeMiddleware */
        $localeMiddleware = $container->get(LocaleMiddleware::class);
        $previous->add(
            action: $localeMiddleware->execute(...),
            position: LocaleMiddleware::MIDDLEWARE_PRIORITY,
        );

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
            get(ResponseFactoryInterface::class),
            get('teknoo.east.common.rendering.clean_html'),
        ),

    //TOTP Plan
    Enable2FA::class => static function (ContainerInterface $container): Enable2FA {
        $defaultErrorTemplate = $container->get('teknoo.east.common.get_default_error_template');

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
        $defaultErrorTemplate = $container->get('teknoo.east.common.get_default_error_template');

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
        $defaultErrorTemplate = $container->get('teknoo.east.common.get_default_error_template');

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

    'teknoo.east.common.symfony.command.minify.css.name' => value('teknoo:common:minify:css'),
    'teknoo.east.common.symfony.command.minify.css.description' => value(
        'Minify a set of css files into an unique file'
    ),
    MinifyCommand::class . ':css' => static function (ContainerInterface $container): MinifyCommand {
        if ($container->has(MessageFactoryInterface::class)) {
            $messageFactory = $container->get(MessageFactoryInterface::class);
        } elseif ($container->has(MessageFactory::class)) {
            $messageFactory = $container->get(MessageFactory::class);
        } else {
            throw new MissingMessageFactoryException(
                'Missing ' . MessageFactoryInterface::class . ' instance in container'
            );
        }

        $finalAssetsLocation = '';
        if ($container->has('teknoo.east.common.assets.final_location')) {
            $finalAssetsLocation = $container->get('teknoo.east.common.assets.final_location');
        }

        return new MinifyCommand(
            $container->get('teknoo.east.common.symfony.command.minify.css.name'),
            $container->get('teknoo.east.common.symfony.command.minify.css.description'),
            $container->get(Executor::class),
            new Client(),
            $container->get(MinifierCommandInterface::class),
            $messageFactory,
            $container->get(SourceLoaderInterface::class . ':css'),
            $container->get(PersisterInterface::class . ':css'),
            $container->get(MinifierInterface::class . ':css'),
            FileType::CSS->value,
            $finalAssetsLocation,
        );
    },

    'teknoo.east.common.symfony.command.minify.js.name' => value('teknoo:common:minify:js'),
    'teknoo.east.common.symfony.command.minify.js.description' => value(
        'Minify a set of javascripts files into an unique file'
    ),
    MinifyCommand::class . ':js' => static function (ContainerInterface $container): MinifyCommand {
        if ($container->has(MessageFactoryInterface::class)) {
            $messageFactory = $container->get(MessageFactoryInterface::class);
        } elseif ($container->has(MessageFactory::class)) {
            $messageFactory = $container->get(MessageFactory::class);
        } else {
            throw new MissingMessageFactoryException(
                'Missing ' . MessageFactoryInterface::class . ' instance in container'
            );
        }

        $finalAssetsLocation = '';
        if ($container->has('teknoo.east.common.assets.final_location')) {
            $finalAssetsLocation = $container->get('teknoo.east.common.assets.final_location');
        }

        return new MinifyCommand(
            $container->get('teknoo.east.common.symfony.command.minify.js.name'),
            $container->get('teknoo.east.common.symfony.command.minify.js.description'),
            $container->get(Executor::class),
            new Client(),
            $container->get(MinifierCommandInterface::class),
            $messageFactory,
            $container->get(SourceLoaderInterface::class . ':js'),
            $container->get(PersisterInterface::class . ':js'),
            $container->get(MinifierInterface::class . ':js'),
            FileType::JS->value,
            $finalAssetsLocation,
        );
    },
];

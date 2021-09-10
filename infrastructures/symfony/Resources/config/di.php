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

namespace Teknoo\East\WebsiteBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormHandling;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormProcessing;
use Teknoo\East\WebsiteBundle\Recipe\Step\RedirectClient;
use Teknoo\East\WebsiteBundle\Recipe\Step\RenderForm;
use Teknoo\East\WebsiteBundle\Recipe\Step\SearchFormLoader;

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

    SearchFormLoaderInterface::class => get(SearchFormLoader::class),

    FormHandlingInterface::class => get(FormHandling::class),

    FormProcessingInterface::class => get(FormProcessing::class),
    FormProcessing::class => create(),

    RedirectClientInterface::class => get(RedirectClient::class),

    RenderFormInterface::class => get(RenderForm::class),
    RenderForm::class => create()
        ->constructor(
            get(EngineInterface::class),
            get(StreamFactoryInterface::class),
            get(ResponseFactoryInterface::class)
        ),
];

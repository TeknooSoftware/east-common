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

namespace Teknoo\East\WebsiteBundle\Resources\config;

use function DI\decorate;
use function DI\object;
use Psr\Container\ContainerInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;

return [
    //Middleware
    LocaleMiddleware::class => object(LocaleMiddleware::class),

    RecipeInterface::class => decorate(function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->registerMiddleware(
                $container->get(LocaleMiddleware::class),
                LocaleMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),
];

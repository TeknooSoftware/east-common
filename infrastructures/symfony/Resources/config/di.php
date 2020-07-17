<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Resources\config;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\WebsiteBundle\Provider\UserProvider;

use function DI\decorate;

return [
    //Middleware
    LocaleMiddleware::class => static function (ContainerInterface $container): LocaleMiddleware {
        return new LocaleMiddleware($container->get('translator'));
    },

    RecipeInterface::class => decorate(static function ($previous, ContainerInterface $container) {
        if ($previous instanceof RecipeInterface) {
            $previous = $previous->registerMiddleware(
                $container->get(LocaleMiddleware::class),
                LocaleMiddleware::MIDDLEWARE_PRIORITY
            );
        }

        return $previous;
    }),

    UserProvider::class => static function (ContainerInterface $container): UserProvider {
        $loader = $container->get(UserLoader::class);

        if (Kernel::VERSION_ID < 50000) {
            return new class ($loader) extends UserProvider implements UserProviderInterface {
                /**
                 * @param string $username
                 */
                public function loadUserByUsername($username)
                {
                    return $this->fetchUserByUsername((string) $username);
                }
            };
        }

        return new class ($loader) extends UserProvider implements UserProviderInterface {
            public function loadUserByUsername(string $username)
            {
                return $this->fetchUserByUsername($username);
            }
        };
    },
];

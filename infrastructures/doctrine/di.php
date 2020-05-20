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

namespace Teknoo\East\Website\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Psr\Container\ContainerInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Doctrine\DBSource\ContentRepository;
use Teknoo\East\Website\Doctrine\DBSource\ItemRepository;
use Teknoo\East\Website\Doctrine\DBSource\Manager;
use Teknoo\East\Website\Doctrine\DBSource\MediaRepository;
use Teknoo\East\Website\Doctrine\DBSource\TypeRepository;
use Teknoo\East\Website\Doctrine\DBSource\UserRepository;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

use function DI\get;
use function DI\create;

return [
    ManagerInterface::class => get(Manager::class),
    Manager::class => create(Manager::class)
        ->constructor(get(ObjectManager::class)),

    ContentRepositoryInterface::class => get(ContentRepository::class),
    ContentRepository::class => function (ContainerInterface $container): ContentRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Content::class);
        if ($repository instanceof ObjectRepository) {
            return new ContentRepository($repository);
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    ItemRepositoryInterface::class => get(ItemRepository::class),
    ItemRepository::class => function (ContainerInterface $container): ItemRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Item::class);
        if ($repository instanceof ObjectRepository) {
            return new ItemRepository($repository);
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    MediaRepositoryInterface::class => get(MediaRepository::class),
    MediaRepository::class => function (ContainerInterface $container): MediaRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof ObjectRepository) {
            return new MediaRepository($repository);
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    TypeRepositoryInterface::class => get(TypeRepository::class),
    TypeRepository::class => function (ContainerInterface $container): TypeRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Type::class);
        if ($repository instanceof ObjectRepository) {
            return new TypeRepository($repository);
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    UserRepositoryInterface::class => get(UserRepository::class),
    UserRepository::class => function (ContainerInterface $container): UserRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(User::class);
        if ($repository instanceof ObjectRepository) {
            return new UserRepository($repository);
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    LocaleMiddleware::class => function (ContainerInterface $container): LocaleMiddleware {
        $listener = $container->get(TranslatableListener::class);

        return new LocaleMiddleware([$listener, 'setTranslatableLocale']);
    },
];

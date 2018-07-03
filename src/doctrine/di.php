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

namespace Teknoo\East\Website\Doctrine;

use function DI\get;
use function DI\create;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Doctrine\DBSource\Manager;
use Teknoo\East\Website\Doctrine\DBSource\RepositoryTrait;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Item;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

return [
    ManagerInterface::class => create(Manager::class)
        ->constructor(get(ObjectManager::class)),

    ContentRepositoryInterface::class => function (ContainerInterface $container): ContentRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Content::class);
        if ($repository instanceof ObjectRepository) {
            return new class($repository) implements ContentRepositoryInterface{
                use RepositoryTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    ItemRepositoryInterface::class => function (ContainerInterface $container): ItemRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Item::class);
        if ($repository instanceof ObjectRepository) {
            return new class($repository) implements ItemRepositoryInterface{
                use RepositoryTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    MediaRepositoryInterface::class => function (ContainerInterface $container): MediaRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof ObjectRepository) {
            return new class($repository) implements MediaRepositoryInterface{
                use RepositoryTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    TypeRepositoryInterface::class => function (ContainerInterface $container): TypeRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Type::class);
        if ($repository instanceof ObjectRepository) {
            return new class($repository) implements TypeRepositoryInterface{
                use RepositoryTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },

    UserRepositoryInterface::class => function (ContainerInterface $container): UserRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(User::class);
        if ($repository instanceof ObjectRepository) {
            return new class($repository) implements UserRepositoryInterface{
                use RepositoryTrait;

            };
        }

        throw new \RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            \get_class($repository)
        ));
    },
];

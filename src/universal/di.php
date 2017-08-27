<?php

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

namespace Teknoo\East\Website;

use function DI\get;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Teknoo\East\Website\Loader\CategoryLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Object\Category;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\CategoryWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;

return [
    CategoryLoader::class => function (ContainerInterface $container) {
        return new CategoryLoader($container->get(ObjectManager::class)->getRepository(Category::class));
    },
    ContentLoader::class => function (ContainerInterface $container) {
        return new ContentLoader($container->get(ObjectManager::class)->getRepository(Content::class));
    },
    'teknoo.east.website.loader.content_loader' => get(ContentLoader::class),
    MediaLoader::class => function (ContainerInterface $container) {
        return new MediaLoader($container->get(ObjectManager::class)->getRepository(Media::class));
    },
    'teknoo.east.website.loader.media_loader' => get(MediaLoader::class),
    TypeLoader::class => function (ContainerInterface $container) {
        return new TypeLoader($container->get(ObjectManager::class)->getRepository(Type::class));
    },
    UserLoader::class => function (ContainerInterface $container) {
        return new UserLoader($container->get(ObjectManager::class)->getRepository(User::class));
    },

    CategoryWriter::class => function (ObjectManager $manager) {
        return new CategoryWriter($manager);
    },
    ContentWriter::class => function (ObjectManager $manager) {
        return new ContentWriter($manager);
    },
    MediaWriter::class => function (ObjectManager $manager) {
        return new MediaWriter($manager);
    },
    TypeWriter::class => function (ObjectManager $manager) {
        return new TypeWriter($manager);
    },
    UserWriter::class => function (ObjectManager $manager) {
        return new UserWriter($manager);
    },

    MenuGenerator::class => function (CategoryLoader $loader) {
        return new MenuGenerator($loader);
    },
    'teknoo.east.website.service.menu_generator' => get(MenuGenerator::class),
];

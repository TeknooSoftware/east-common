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

namespace Teknoo\East\Common\Flysystem;

use DomainException;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Flysystem\FrontAsset\Persister;
use Teknoo\East\Common\Flysystem\FrontAsset\SourceLoader;

use function DI\get;

return [
    PersisterInterface::class . ':css' => get(Persister::class . ':css'),
    Persister::class . ':css' => static function (ContainerInterface $container): Persister {
        if (!$container->has('teknoo.east.common.assets.destination.css.path')) {
            throw new DomainException(
                '`teknoo.east.common.assets.destination.css.path` is not defined',
            );
        }

        return new Persister(
            new Filesystem(
                new LocalFilesystemAdapter(
                    (string) $container->get('teknoo.east.common.assets.destination.css.path'),
                ),
            ),
        );
    },

    SourceLoaderInterface::class . ':css' => get(SourceLoader::class . ':css'),
    SourceLoader::class . ':css' => static function (ContainerInterface $container): Persister {
        if (!$container->has('teknoo.east.common.assets.destination.css.path')) {
            throw new DomainException(
                '`teknoo.east.common.assets.source.css.path` is not defined',
            );
        }

        return new Persister(
            new Filesystem(
                new LocalFilesystemAdapter(
                    (string) $container->get('teknoo.east.common.assets.source.css.path'),
                ),
            ),
        );
    },

    PersisterInterface::class . ':js' => get(Persister::class . ':js'),
    Persister::class . ':js' => static function (ContainerInterface $container): Persister {
        if (!$container->has('teknoo.east.common.assets.destination.css.path')) {
            throw new DomainException(
                '`teknoo.east.common.assets.destination.js.path` is not defined',
            );
        }

        return new Persister(
            new Filesystem(
                new LocalFilesystemAdapter(
                    (string) $container->get('teknoo.east.common.assets.destination.js.path'),
                ),
            ),
        );
    },

    SourceLoaderInterface::class . ':js' => get(SourceLoader::class . ':js'),
    SourceLoader::class . ':js' => static function (ContainerInterface $container): Persister {
        if (!$container->has('teknoo.east.common.assets.destination.css.path')) {
            throw new DomainException(
                '`teknoo.east.common.assets.source.js.path` is not defined',
            );
        }

        return new Persister(
            new Filesystem(
                new LocalFilesystemAdapter(
                    (string) $container->get('teknoo.east.common.assets.source.js.path'),
                ),
            ),
        );
    },
];

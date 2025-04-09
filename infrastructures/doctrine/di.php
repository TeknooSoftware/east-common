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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\Manager;
use Teknoo\East\Common\Doctrine\DBSource\Common\MediaRepository;
use Teknoo\East\Common\Doctrine\DBSource\Common\UserRepository;
use Teknoo\East\Common\Doctrine\DBSource\Exception\NonManagedRepositoryException;
use Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager;
use Teknoo\East\Common\Doctrine\DBSource\ODM\MediaRepository as OdmMediaRepository;
use Teknoo\East\Common\Doctrine\DBSource\ODM\UserRepository as OdmUserRepository;
use Teknoo\East\Common\Doctrine\Exception\NotSupportedException;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;
use Teknoo\East\Common\Doctrine\Writer\ODM\MediaWriter;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Writer\MediaWriter as OriginalWriter;

use function DI\create;
use function DI\get;

return [
    ManagerInterface::class => get(Manager::class),
    Manager::class => create()->constructor(
        get(ObjectManager::class),
    ),

    BatchManipulationManagerInterface::class => get(BatchManipulationManager::class),
    BatchManipulationManager::class => create()->constructor(
        get(ManagerInterface::class),
        get('doctrine_mongodb.odm.default_document_manager'),
    ),

    UserRepositoryInterface::class => static function (ContainerInterface $container): UserRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(User::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmUserRepository($repository);
        }

        return new UserRepository($repository);
    },

    MediaRepositoryInterface::class => static function (ContainerInterface $container): MediaRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmMediaRepository($repository);
        }

        return new MediaRepository($repository);
    },

    MediaWriter::class => static function (ContainerInterface $container): MediaWriter {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof GridFSRepository) {
            return new MediaWriter($repository, $container->get(OriginalWriter::class));
        }

        throw new NotSupportedException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },
    'teknoo.east.common.doctrine.writer.media.new' => get(MediaWriter::class),

    GetStreamFromMediaInterface::class => get(GetStreamFromMedia::class),
    GetStreamFromMedia::class => static function (ContainerInterface $container): GetStreamFromMedia {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if (!$repository instanceof GridFSRepository) {
            throw new NotSupportedException('Repository for Media class is not a GridFSRepository');
        }

        return new GetStreamFromMedia(
            $repository,
            $container->get(StreamFactoryInterface::class)
        );
    },
];

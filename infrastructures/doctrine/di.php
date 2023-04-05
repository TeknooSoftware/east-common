<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
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

namespace Teknoo\East\Common\Doctrine;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use ProxyManager\Proxy\GhostObjectInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Contracts\Service\ProxyDetectorInterface;
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
use Teknoo\Recipe\Promise\PromiseInterface;

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

        if ($repository instanceof ObjectRepository) {
            return new UserRepository($repository);
        }

        throw new NonManagedRepositoryException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    MediaRepositoryInterface::class => static function (ContainerInterface $container): MediaRepositoryInterface {
        $repository = $container->get(ObjectManager::class)->getRepository(Media::class);
        if ($repository instanceof DocumentRepository) {
            return new OdmMediaRepository($repository);
        }

        if ($repository instanceof ObjectRepository) {
            return new MediaRepository($repository);
        }

        throw new NotSupportedException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    ProxyDetectorInterface::class => static function (): ProxyDetectorInterface {
        return new class implements ProxyDetectorInterface {
            public function checkIfInstanceBehindProxy(
                object $object,
                PromiseInterface $promise
            ): ProxyDetectorInterface {
                if (!$object instanceof GhostObjectInterface) {
                    $promise->fail(new Exception('Object is not behind a proxy', 500));

                    return $this;
                }

                if ($object->isProxyInitialized()) {
                    $promise->fail(new Exception('Proxy is already initialized', 500));

                    return $this;
                }

                $promise->success($object);

                return $this;
            }
        };
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

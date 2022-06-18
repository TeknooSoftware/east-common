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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use ProxyManager\Proxy\GhostObjectInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\BatchManipulationManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Service\ProxyDetectorInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\Manager;
use Teknoo\East\Common\Doctrine\DBSource\Common\UserRepository;
use Teknoo\East\Common\Doctrine\DBSource\ODM\BatchManipulationManager;
use Teknoo\East\Common\Doctrine\DBSource\ODM\UserRepository as OdmUserRepository;
use Teknoo\East\Common\Object\User;
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

        throw new RuntimeException(sprintf(
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
];

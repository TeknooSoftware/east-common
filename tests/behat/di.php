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

namespace Teknoo\Tests\East\Common\Behat;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\Tests\East\Common\Behat\Loader\MyObjectLoader;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;
use Teknoo\Tests\East\Common\Behat\Repository\MyObjectRepository;
use Teknoo\Tests\East\Common\Behat\Writer\MyObjectWriter;
use function DI\create;
use function DI\get;

return [
    MyObjectRepository::class => static function (ContainerInterface $container): MyObjectRepository {
        $repository = $container->get(ObjectManager::class)->getRepository(MyObject::class);
        if ($repository instanceof ObjectRepository) {
            return new MyObjectRepository($repository);
        }

        throw new RuntimeException(sprintf(
            "Error, repository of class %s are not currently managed",
            $repository::class
        ));
    },

    MyObjectLoader::class => create()
        ->constructor(
            get(MyObjectRepository::class),
        ),

    MyObjectWriter::class => create()
        ->constructor(
            get(ManagerInterface::class),
            get(DatesService::class),
        ),

    'teknoo.east.common.deleting.my_object' => create(DeletingService::class)
        ->constructor(get(MyObjectWriter::class), get(DatesService::class)),
];

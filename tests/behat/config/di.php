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

namespace Teknoo\Tests\East\Common\Behat;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Tests\East\Common\Behat\Loader\MyObjectLoader;
use Teknoo\Tests\East\Common\Behat\Loader\MyObjectTimeStampableLoader;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;
use Teknoo\Tests\East\Common\Behat\Repository\MyObjectRepository;
use Teknoo\Tests\East\Common\Behat\Writer\MyObjectTimeStampableWriter;
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

    MyObjectTimeStampableLoader::class => create()
        ->constructor(
            get(MyObjectRepository::class),
        ),

    MyObjectTimeStampableWriter::class => create()
        ->constructor(
            get(ManagerInterface::class),
            get(DatesService::class),
        ),

    'teknoo.east.common.deleting.my_object_timestampable' => create(DeletingService::class)
        ->constructor(get(MyObjectTimeStampableWriter::class), get(DatesService::class)),

    'teknoo.east.common.assets.destination.css.path' => 'tests/support/build/css',
    'teknoo.east.common.assets.source.css.path' => 'tests/support',
    'teknoo.east.common.assets.destination.js.path' => 'tests/support/build/js',
    'teknoo.east.common.assets.source.js.path' => 'tests/support/js',
    'teknoo.east.common.assets.final_location' => 'build/',

    'teknoo.east.common.assets.sets.css' => [
        'main' => [
            'css/file1.css',
            'css/file3.css',
        ],
    ],

    'teknoo.east.common.assets.sets.js' => [
        'main' => [
            'file1.js',
            'file3.js',
        ],
    ],
];

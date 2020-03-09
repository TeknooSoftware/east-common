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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Infrastructure;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Gedmo\Translatable\TranslatableListener;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\Middleware\LocaleMiddleware;
use Teknoo\East\Website\Doctrine\Object\Content;
use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;
use function DI\string;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return Container
     * @throws \Exception
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../infrastructures/di.php');

        return $containerDefinition->build();
    }

    public function testResponseFactoryInterface()
    {
        $container = $this->buildContainer();
        $factory = $container->get(ResponseFactoryInterface::class);
        self::assertInstanceOf(ResponseFactory::class, $factory);
        self::assertInstanceOf(ResponseFactoryInterface::class, $factory);
    }

    public function testStreamFactoryInterface()
    {
        $container = $this->buildContainer();
        $factory = $container->get(StreamFactoryInterface::class);
        self::assertInstanceOf(StreamFactoryInterface::class, $factory);
        self::assertInstanceOf(CallbackStreamFactory::class, $factory);
    }

    public function testResponseFactoryInterfaceInvalid()
    {
        $this->expectException(\RuntimeException::class);

        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../infrastructures/di.php');
        $containerDefinition->addDefinitions([
            ResponseFactoryInterface::class.':value' => string('fooBar')
        ]);

        $container = $containerDefinition->build();
        $factory = $container->get(ResponseFactoryInterface::class);
        self::assertInstanceOf(ResponseFactory::class, $factory);
        self::assertInstanceOf(ResponseFactoryInterface::class, $factory);
    }

    public function testStreamFactoryInterfaceInvalid()
    {
        $this->expectException(\RuntimeException::class);

        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../infrastructures/di.php');
        $containerDefinition->addDefinitions([
            StreamFactoryInterface::class.':value' => string('fooBar')
        ]);

        $container = $containerDefinition->build();
        $factory = $container->get(StreamFactoryInterface::class);
        self::assertInstanceOf(StreamFactoryInterface::class, $factory);
        self::assertInstanceOf(CallbackStreamFactory::class, $factory);
    }
}

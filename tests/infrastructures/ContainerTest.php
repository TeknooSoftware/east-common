<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\Common\Infrastructure;

use DI\Container;
use DI\ContainerBuilder;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\Message\CallbackStreamFactoryInterface;
use function DI\string;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
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
    }

    public function testCallbackStreamFactoryInterface()
    {
        $container = $this->buildContainer();
        $factory = $container->get(CallbackStreamFactoryInterface::class);
        self::assertInstanceOf(StreamFactoryInterface::class, $factory);
        self::assertInstanceOf(CallbackStreamFactory::class, $factory);
    }
}

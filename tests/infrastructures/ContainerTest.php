<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../infrastructures/di.php');

        return $containerDefinition->build();
    }

    public function testResponseFactoryInterface(): void
    {
        $container = $this->buildContainer();
        $factory = $container->get(ResponseFactoryInterface::class);
        $this->assertInstanceOf(ResponseFactory::class, $factory);
        $this->assertInstanceOf(ResponseFactoryInterface::class, $factory);
    }

    public function testStreamFactoryInterface(): void
    {
        $container = $this->buildContainer();
        $factory = $container->get(StreamFactoryInterface::class);
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory);
    }

    public function testCallbackStreamFactoryInterface(): void
    {
        $container = $this->buildContainer();
        $factory = $container->get(CallbackStreamFactoryInterface::class);
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory);
        $this->assertInstanceOf(CallbackStreamFactory::class, $factory);
    }
}

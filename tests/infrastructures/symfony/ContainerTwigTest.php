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

namespace Teknoo\Tests\East\CommonBundle;

use DI\Container;
use DI\ContainerBuilder;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Twig\Template\Engine;
use Twig\Environment;

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
class ContainerTwigTest extends TestCase
{
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/symfony/Resources/config/twig.php');
        $containerDefinition->useAutowiring(false);

        return $containerDefinition->build();
    }

    public function testEngineInterface(): void
    {
        $container = $this->buildContainer();
        $container->set('twig', $this->createStub(Environment::class));

        $this->assertInstanceOf(
            EngineInterface::class,
            $container->get(EngineInterface::class)
        );
    }

    public function testEngine(): void
    {
        $container = $this->buildContainer();
        $container->set('twig', $this->createStub(Environment::class));

        $this->assertInstanceOf(
            Engine::class,
            $container->get(Engine::class)
        );
    }
}

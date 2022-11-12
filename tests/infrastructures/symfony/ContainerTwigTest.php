<?php

/**
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTwigTest extends TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/symfony/Resources/config/twig.php');
        $containerDefinition->useAutowiring(false);
        
        return $containerDefinition->build();
    }

    public function testEngineInterface()
    {
        $container = $this->buildContainer();
        $container->set('twig', $this->createMock(Environment::class));

        self::assertInstanceOf(
            EngineInterface::class,
            $container->get(EngineInterface::class)
        );
    }

    public function testEngine()
    {
        $container = $this->buildContainer();
        $container->set('twig', $this->createMock(Environment::class));

        self::assertInstanceOf(
            Engine::class,
            $container->get(Engine::class)
        );
    }
}

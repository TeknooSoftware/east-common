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

namespace Teknoo\Tests\East\Common\Minify;

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Minify\Css\Minifier as CssMinifier;
use Teknoo\East\Common\Minify\Js\Minifier as JsMinifier;

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
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/minify/di.php');

        return $containerDefinition->build();
    }

    public function testMinifierInterfaceCss(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            MinifierInterface::class,
            $container->get(MinifierInterface::class . ':css')
        );

        $this->assertInstanceOf(
            MinifierInterface::class,
            $container->get(CssMinifier::class)
        );
    }

    public function testMinifierInterfaceJs(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            MinifierInterface::class,
            $container->get(MinifierInterface::class . ':js')
        );

        $this->assertInstanceOf(
            MinifierInterface::class,
            $container->get(JsMinifier::class)
        );
    }
}

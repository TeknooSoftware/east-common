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

namespace Teknoo\Tests\East\WebsiteBundle;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;

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
    public function testLegacyDIForward()
    {
        $di1 = include __DIR__.'/../../../src/symfony/Resources/config/di.php';
        $di2 = include __DIR__.'/../../../infrastructures/symfony/Resources/config/di.php';

        self::assertEquals(
            $di1,
            $di2
        );
    }

    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../vendor/teknoo/east-foundation/src/universal/di.php');
        $containerDefinition->addDefinitions(__DIR__.'/../../../src/universal/di.php');
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/symfony/Resources/config/di.php');
        $containerDefinition->useAutowiring(false);
        
        return $containerDefinition->build();
    }

    public function testLocaleMiddleware()
    {
        $container = $this->buildContainer();
        $translatableListener = $this->createMock(LocaleAwareInterface::class);

        $container->set('translator', $translatableListener);
        $loader = $container->get(LocaleMiddleware::class);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $loader
        );
    }

    public function testEastManagerMiddlewareInjection()
    {
        $container = $this->buildContainer();

        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set('translator', $this->createMock(LocaleAwareInterface::class));

        self::assertInstanceOf(
            RecipeInterface::class,
            $container->get(RecipeInterface::class)
        );
    }
}

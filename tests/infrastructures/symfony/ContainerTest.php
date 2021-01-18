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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Object\User as BaseUser;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\Website\Service\DatesService;
use Teknoo\East\WebsiteBundle\Middleware\LocaleMiddleware;
use Teknoo\East\WebsiteBundle\Object\User;
use Teknoo\East\WebsiteBundle\Provider\UserProvider;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormHandling;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormProcessing;
use Teknoo\East\WebsiteBundle\Recipe\Step\RedirectClient;
use Teknoo\East\WebsiteBundle\Recipe\Step\RenderForm;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTest extends TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../../src/di.php');
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
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));
        $container->set('translator', $this->createMock(LocaleAwareInterface::class));

        self::assertInstanceOf(
            RecipeInterface::class,
            $container->get(RecipeInterface::class)
        );
    }

    public function testUserProvider()
    {
        $container = $this->buildContainer();

        $container->set(UserLoader::class, $loader = $this->createMock(UserLoader::class));

        self::assertInstanceOf(
            UserProvider::class,
            $provider = $container->get(UserProvider::class)
        );

        $user = new BaseUser();
        $user->setEmail('foo@bar');

        $loader->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user, $loader) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $loader;
            });

        self::assertEquals(
            (new User($user)),
            $provider->loadUserByUsername('foo@bar')
        );
    }

    public function testFormHandling()
    {
        $container = $this->buildContainer();

        $container->set(DatesService::class, $this->createMock(DatesService::class));
        $container->set('form.factory', $this->createMock(FormFactoryInterface::class));

        self::assertInstanceOf(
            FormHandlingInterface::class,
            $container->get(FormHandlingInterface::class)
        );

        self::assertInstanceOf(
            FormHandling::class,
            $container->get(FormHandling::class)
        );
    }

    public function testFormProcessing()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            FormProcessing::class,
            $container->get(FormProcessing::class)
        );

        self::assertInstanceOf(
            FormProcessingInterface::class,
            $container->get(FormProcessingInterface::class)
        );
    }

    public function testRedirectClient()
    {
        $container = $this->buildContainer();

        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));
        $container->set('router', $this->createMock(UrlGeneratorInterface::class));

        self::assertInstanceOf(
            RedirectClientInterface::class,
            $container->get(RedirectClientInterface::class)
        );

        self::assertInstanceOf(
            RedirectClient::class,
            $container->get(RedirectClient::class)
        );
    }

    public function testRenderForm()
    {
        $container = $this->buildContainer();

        $container->set(EngineInterface::class, $this->createMock(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            RenderFormInterface::class,
            $container->get(RenderFormInterface::class)
        );

        self::assertInstanceOf(
            RenderForm::class,
            $container->get(RenderForm::class)
        );
    }
}

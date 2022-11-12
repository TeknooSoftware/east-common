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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Common\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User as BaseUser;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;
use Teknoo\East\CommonBundle\Object\LegacyUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\User;
use Teknoo\East\CommonBundle\Provider\UserProvider;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;
use Teknoo\Recipe\Promise\PromiseInterface;

use function interface_exists;
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

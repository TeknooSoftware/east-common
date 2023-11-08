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

namespace Teknoo\Tests\East\CommonBundle;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierCommandInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Command\MinifyCommand;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Disable2FA;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Enable2FA;
use Teknoo\East\CommonBundle\Recipe\Cookbook\GenerateQRCode;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Validate2FA;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\EnableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\ValidateTOTP;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;

use Teknoo\East\Common\Contracts\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use function interface_exists;
/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
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

    public function testEnable2FA()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(EnableTOTP::class, $this->createMock(EnableTOTP::class));
        $container->set(CreateObject::class, $this->createMock(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            Enable2FA::class,
            $container->get(Enable2FA::class)
        );
    }

    public function testDisable2FA()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(DisableTOTP::class, $this->createMock(DisableTOTP::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            Disable2FA::class,
            $container->get(Disable2FA::class)
        );
    }

    public function testGenerateQRCode()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(GenerateQRCodeTextForTOTP::class, $this->createMock(GenerateQRCodeTextForTOTP::class));
        $container->set(BuildQrCodeInterface::class, $this->createMock(BuildQrCodeInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            GenerateQRCode::class,
            $container->get(GenerateQRCode::class)
        );
    }

    public function testValidate2FA()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createMock(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createMock(FormProcessingInterface::class));
        $container->set(ValidateTOTP::class, $this->createMock(ValidateTOTP::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            Validate2FA::class,
            $container->get(Validate2FA::class)
        );
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
        $container->set('translator', $this->createMock(LocaleAwareInterface::class));

        self::assertInstanceOf(
            RecipeInterface::class,
            $container->get(RecipeInterface::class)
        );
    }

    public function testMinifyCommandCssWithMessageFactoryInterface()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createMock(MinifierInterface::class));
        $container->set(MessageFactoryInterface::class, $this->createMock(MessageFactoryInterface::class));

        self::assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':css')
        );
    }

    public function testMinifyCommandCssWith()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createMock(MinifierInterface::class));
        $container->set(MessageFactory::class, $this->createMock(MessageFactory::class));

        self::assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':css')
        );
    }

    public function testMinifyCommandCssWithoutMessageFactory()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createMock(MinifierInterface::class));

        $this->expectException(RuntimeException::class);
        $container->get(MinifyCommand::class . ':css');
    }

    public function testMinifyCommandJsWithMessageFactoryInterface()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createMock(MinifierInterface::class));
        $container->set(MessageFactoryInterface::class, $this->createMock(MessageFactoryInterface::class));

        self::assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':js')
        );
    }

    public function testMinifyCommandJsWith()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createMock(MinifierInterface::class));
        $container->set(MessageFactory::class, $this->createMock(MessageFactory::class));

        self::assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':js')
        );
    }

    public function testMinifyCommandJsWithoutMessageFactory()
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(Executor::class, $this->createMock(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createMock(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createMock(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createMock(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createMock(MinifierInterface::class));

        $this->expectException(RuntimeException::class);
        $container->get(MinifyCommand::class . ':js');
    }
}

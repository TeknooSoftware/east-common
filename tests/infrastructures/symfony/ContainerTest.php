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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Command\MinifyCommand;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;
use Teknoo\East\CommonBundle\Recipe\Plan\Disable2FA;
use Teknoo\East\CommonBundle\Recipe\Plan\Enable2FA;
use Teknoo\East\CommonBundle\Recipe\Plan\GenerateQRCode;
use Teknoo\East\CommonBundle\Recipe\Plan\Validate2FA;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\EnableTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\East\CommonBundle\Recipe\Step\ValidateTOTP;
use Teknoo\East\Diactoros\MessageFactory;
use Teknoo\East\Foundation\Command\Executor;
use Teknoo\East\Foundation\Http\Message\MessageFactoryInterface;
use Teknoo\East\Foundation\Recipe\PlanInterface;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../../src/di.php');
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/symfony/config/di.php');
        $containerDefinition->useAutowiring(false);

        return $containerDefinition->build();
    }

    public function testFormProcessing(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            FormProcessing::class,
            $container->get(FormProcessing::class)
        );

        $this->assertInstanceOf(
            FormProcessingInterface::class,
            $container->get(FormProcessingInterface::class)
        );
    }

    public function testRenderForm(): void
    {
        $container = $this->buildContainer();

        $container->set(EngineInterface::class, $this->createStub(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            RenderFormInterface::class,
            $container->get(RenderFormInterface::class)
        );

        $this->assertInstanceOf(
            RenderForm::class,
            $container->get(RenderForm::class)
        );
    }

    public function testEnable2FA(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.plan.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(EnableTOTP::class, $this->createStub(EnableTOTP::class));
        $container->set(CreateObject::class, $this->createStub(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));

        $this->assertInstanceOf(
            Enable2FA::class,
            $container->get(Enable2FA::class)
        );
    }

    public function testPlanInterface(): void
    {
        $container = $this->buildContainer();
        $container->set(\Teknoo\East\Common\Middleware\LocaleMiddleware::class, $this->createStub(\Teknoo\East\Common\Middleware\LocaleMiddleware::class));
        $container->set(LocaleMiddleware::class, $this->createStub(LocaleMiddleware::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));

        $this->assertInstanceOf(
            PlanInterface::class,
            $container->get(PlanInterface::class),
        );
    }

    public function testDisable2FA(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.plan.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(DisableTOTP::class, $this->createStub(DisableTOTP::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));

        $this->assertInstanceOf(
            Disable2FA::class,
            $container->get(Disable2FA::class)
        );
    }

    public function testGenerateQRCode(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.plan.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(GenerateQRCodeTextForTOTP::class, $this->createStub(GenerateQRCodeTextForTOTP::class));
        $container->set(BuildQrCodeInterface::class, $this->createStub(BuildQrCodeInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));

        $this->assertInstanceOf(
            GenerateQRCode::class,
            $container->get(GenerateQRCode::class)
        );
    }

    public function testValidate2FA(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.plan.default_error_template', 'foo');

        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createStub(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(ValidateTOTP::class, $this->createStub(ValidateTOTP::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));

        $this->assertInstanceOf(
            Validate2FA::class,
            $container->get(Validate2FA::class)
        );
    }

    public function testLocaleMiddleware(): void
    {
        $container = $this->buildContainer();
        $translatableListener = $this->createStub(LocaleAwareInterface::class);

        $container->set('translator', $translatableListener);
        $loader = $container->get(LocaleMiddleware::class);

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $loader
        );
    }

    public function testEastManagerMiddlewareInjection(): void
    {
        $container = $this->buildContainer();

        $container->set(LoggerInterface::class, $this->createStub(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set('translator', $this->createStub(LocaleAwareInterface::class));

        $this->assertInstanceOf(
            RecipeInterface::class,
            $container->get(RecipeInterface::class)
        );
    }

    public function testMinifyCommandCssWithMessageFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createStub(MinifierInterface::class));
        $container->set(MessageFactoryInterface::class, $this->createStub(MessageFactoryInterface::class));

        $this->assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':css')
        );
    }

    public function testMinifyCommandCssWithMessageFactory(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createStub(MinifierInterface::class));
        $container->set(MessageFactory::class, $this->createStub(MessageFactory::class));

        $this->assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':css')
        );
    }

    public function testMinifyCommandCssWithoutMessageFactory(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.css.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.css.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':css', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':css', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':css', $this->createStub(MinifierInterface::class));

        $this->expectException(RuntimeException::class);
        $container->get(MinifyCommand::class . ':css');
    }

    public function testMinifyCommandJsWithMessageFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createStub(MinifierInterface::class));
        $container->set(MessageFactoryInterface::class, $this->createStub(MessageFactoryInterface::class));

        $this->assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':js')
        );
    }

    public function testMinifyCommandJsWithMessageFactory(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createStub(MinifierInterface::class));
        $container->set(MessageFactory::class, $this->createStub(MessageFactory::class));

        $this->assertInstanceOf(
            MinifyCommand::class,
            $container->get(MinifyCommand::class . ':js')
        );
    }

    public function testMinifyCommandJsWithoutMessageFactory(): void
    {
        $container = $this->buildContainer();

        $container->set('teknoo.east.common.symfony.command.minify.js.name', 'foo');
        $container->set('teknoo.east.common.symfony.command.minify.js.description', 'foo');
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );
        $container->set(Executor::class, $this->createStub(Executor::class));
        $container->set(MinifierCommandInterface::class, $this->createStub(MinifierCommandInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));
        $container->set(SourceLoaderInterface::class . ':js', $this->createStub(SourceLoaderInterface::class));
        $container->set(PersisterInterface::class . ':js', $this->createStub(PersisterInterface::class));
        $container->set(MinifierInterface::class . ':js', $this->createStub(MinifierInterface::class));

        $this->expectException(RuntimeException::class);
        $container->get(MinifyCommand::class . ':js');
    }
}

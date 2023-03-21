<?php

/*
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
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
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
}

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

namespace Teknoo\East\Common\Recipe\Step;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Throwable;

use function str_replace;

/**
 * Recipe step to render errors page when an error or exception has been thrown during the recipe's execution.
 * Like Render step, Views parameters are fetched from the Parameters Bag and transformed to an array.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RenderError
{
    use TemplateTrait;

    public function __construct(
        EngineInterface $templating,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory,
        private bool $defaultCleanHtml = false,
    ) {
        $this->templating = $templating;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param array<string, mixed> $viewParameters
     */
    public function __invoke(
        MessageInterface $message,
        ClientInterface $client,
        string $errorTemplate,
        Throwable $error,
        ManagerInterface $manager,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
        ?string $api = null,
        ?bool $cleanHtml = null,
    ): self {
        $manager->stopErrorReporting();
        $viewParameters = ['error' => $error] + $viewParameters;

        $errorCode = (int) $error->getCode();
        if (empty($errorCode)) {
            $errorCode = 500;
        }

        $errorView = match ($errorCode) {
            400 => (string) $errorCode,
            401 => (string) $errorCode,
            402 => (string) $errorCode,
            403 => (string) $errorCode,
            404 => (string) $errorCode,
            500 => (string) $errorCode,
            default => 'server',
        };

        $errorTemplate = str_replace('<error>', $errorView, $errorTemplate);

        $client->errorInRequest($error, true);

        $this->render(
            client: $client,
            view: $errorTemplate,
            parameters: $viewParameters,
            status: $errorCode,
            api: $api,
            cleanHtml: $cleanHtml ?? $this->defaultCleanHtml,
        );

        return $this;
    }
}

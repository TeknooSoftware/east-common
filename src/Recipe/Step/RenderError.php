<?php

/*
 * East Website.
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Website\View\ParametersBag;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Throwable;

use function str_replace;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RenderError
{
    use TemplateTrait;

    public function __construct(
        EngineInterface $templating,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory,
    ) {
        $this->templating = $templating;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    public function __invoke(
        MessageInterface $message,
        ClientInterface $client,
        string $errorTemplate,
        Throwable $error,
        ManagerInterface $manager,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
    ): self {
        $manager->stopErrorReporting();

        if ($message instanceof ServerRequestInterface) {
            $viewParameters += $message->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);
        }
        $viewParameters = ['error' => $error] + $viewParameters;

        $errorCode = $error->getCode();
        if (empty($errorCode)) {
            $errorCode = 500;
        }

        $errorView = 'server';
        switch ($errorCode) {
            case 400:
            case 401:
            case 402:
            case 403:
            case 404:
                $errorView = (string) $errorCode;
        }

        $errorTemplate = str_replace('<error>', $errorView, $errorTemplate);

        $client->errorInRequest($error, true);

        $this->render(
            $client,
            $errorTemplate,
            $viewParameters,
            $errorCode
        );

        return $this;
    }
}

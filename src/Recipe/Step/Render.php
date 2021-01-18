<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Render
{
    use TemplateTrait;

    public function __construct(
        EngineInterface $templating,
        StreamFactoryInterface $streamFactory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->templating = $templating;
        $this->streamFactory = $streamFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param mixed $objectInstance
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $template,
        ?string $objectViewKey = null,
        $objectInstance = null
    ): self {
        $viewParameters = $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);

        if (!empty($objectViewKey)) {
            $viewParameters = \array_merge($viewParameters, [$objectViewKey => $objectInstance]);
        }

        $headers = [];
        if (
            \is_object($objectInstance)
            && \method_exists($objectInstance, 'updatedAt')
            && null !== ($updatedAt = $updatedAt = $objectInstance->updatedAt())
        ) {
            $headers['Last-Modified'] = $updatedAt->format('D, d M Y H:i:s \G\M\T');
        }

        $this->render(
            $client,
            $template,
            $viewParameters,
            200,
            $headers
        );

        return $this;
    }
}

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
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Website\View\ParametersBag;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

use function is_object;
use function method_exists;

/**
 * Step recipe to render a view thanks a template engine. The template to call must be passed as ingredient.
 * The object instance, referenced as `$objectViewKey` ingredient will be automatically passed but is not mandatory.
 * It will be passed to the view in the variable defined in the ingredient `$objectViewKey` (if defined)
 *
 * Views parameters are fetched from the Parameters Bag and transformed to an array.
 * They can also be passed in message's attribute `view.parameters` (legacy behavior, deprecated)
 *
 * If the object instance is publishable, the `Last-Modified` will be added
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Render
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

    /**
     * @param array<string, mixed> $viewParameters
     */
    public function __invoke(
        MessageInterface $message,
        ClientInterface $client,
        string $template,
        ?string $objectViewKey = null,
        mixed $objectInstance = null,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
    ): self {
        if (!empty($objectViewKey)) {
            $viewParameters = [$objectViewKey => $objectInstance] + $viewParameters;
        }

        $headers = [];
        if (
            is_object($objectInstance)
            && method_exists($objectInstance, 'updatedAt')
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

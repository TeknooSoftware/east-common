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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Step;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

use function is_object;
use function method_exists;

/**
 * Step recipe to render a view thanks a template engine. The template to call must be passed as ingredient.
 * The object instance, referenced as `$objectViewKey` ingredient will be automatically passed but is not mandatory.
 * It will be passed to the view in the variable defined in the ingredient `$objectViewKey` (if defined)
 *
 * Views parameters are fetched from the Parameters Bag and transformed to an array.
 *
 * If the object instance is publishable, the `Last-Modified` will be added
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Render
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
        string $template,
        ?string $objectViewKey = null,
        mixed $objectInstance = null,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
        ?string $api = null,
        #[Transform(transformer: 'boolval')]
        ?bool $cleanHtml = null,
    ): self {
        if (!empty($objectViewKey)) {
            $viewParameters = [$objectViewKey => $objectInstance] + $viewParameters;
        }

        $headers = [];
        if (
            is_object($objectInstance)
            && method_exists($objectInstance, 'updatedAt')
            && null !== ($updatedAt = $objectInstance->updatedAt())
        ) {
            $headers['Last-Modified'] = $updatedAt->format('D, d M Y H:i:s \G\M\T');
        }

        $this->render(
            client: $client,
            view: $template,
            parameters: $viewParameters,
            status: 200,
            headers: $headers,
            api: $api,
            cleanHtml: $cleanHtml ?? $this->defaultCleanHtml,
        );

        return $this;
    }
}

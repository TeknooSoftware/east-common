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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

/**
 * Recipe step to render, via a template engine, a list of persisted object, loaded thanks to LoadListObjects steps.
 *
 * Views parameters are fetched from the Parameters Bag and transformed to an array.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RenderList
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
     * @param iterable<ObjectInterface> $objectsCollection
     * @param array<string, mixed> $viewParameters
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        iterable $objectsCollection,
        int $pageCount,
        int $itemsPerPage,
        int $page,
        string $template,
        mixed $searchForm = null,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
        ?string $api = null,
        #[Transform(transformer: 'boolval')]
        ?bool $cleanHtml = null,
    ): self {
        $this->render(
            client: $client,
            view: $template,
            parameters: [
                'objectsCollection' => $objectsCollection,
                'page' => $page,
                'pageCount' => $pageCount,
                'queryParams' => $request->getQueryParams(),
                'searchForm' => $searchForm,
            ] + $viewParameters,
            api: $api,
            cleanHtml: $cleanHtml ?? $this->defaultCleanHtml,
        );

        return $this;
    }
}

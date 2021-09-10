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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Website\View\ParametersBag;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

/**
 * Recipe step to render, via a template engine, a list of persisted object, loaded thanks to LoadListObjects steps.
 *
 * Views parameters are fetched from the Parameters Bag and transformed to an array.
 * They can also be passed in message's attribute `view.parameters` (legacy behavior, deprecated)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RenderList
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
    ): self {
        $this->render(
            $client,
            $template,
            [
                'objectsCollection' => $objectsCollection,
                'page' => $page,
                'pageCount' => $pageCount,
                'queryParams' => $request->getQueryParams(),
                'searchForm' => $searchForm
            ]
            + $viewParameters,
        );

        return $this;
    }
}

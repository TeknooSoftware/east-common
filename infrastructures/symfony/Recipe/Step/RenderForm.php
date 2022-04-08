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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to render thanks to a template engine the form instance
 * from the workplan.
 * Symfony implementation for `RenderFormInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RenderForm implements RenderFormInterface
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
        ServerRequestInterface $request,
        ClientInterface $client,
        mixed $form,
        string $template,
        ObjectInterface $object,
        bool $isTranslatable = false,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
    ): RenderFormInterface {
        $parameters = [
            'objectInstance' => $object,
            'request' => $request,
            'formView' => null,
            'isTranslatable' => $isTranslatable
        ];

        if ($form instanceof FormInterface) {
            $parameters['formView'] = $form->createView();
        }

        $this->render(
            $client,
            $template,
            $parameters
            + $viewParameters
        );

        return $this;
    }
}

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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use DateTimeInterface;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RenderForm implements RenderFormInterface
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
        ServerRequestInterface $request,
        ClientInterface $client,
        mixed $form,
        string $template,
        ObjectInterface $object,
        bool $isTranslatable = false,
        #[Transform(ParametersBag::class)] array $viewParameters = [],
        bool|string|null $objectSaved = null,
        ?string $api = null,
        #[Transform(transformer: 'boolval')]
        ?bool $cleanHtml = null,
    ): RenderFormInterface {
        $parameters = [
            'objectInstance' => $object,
            'request' => $request,
            'formView' => null,
            'isTranslatable' => $isTranslatable
        ];

        if (
            !isset($viewParameters['objectSaved'])
            && null !== $objectSaved
        ) {
            $viewParameters['objectSaved'] = !empty($objectSaved);
        }

        if ($form instanceof FormInterface) {
            $parameters['formView'] = $form->createView();
        }

        $status = 200;
        if (
            !empty($api)
            && $form instanceof FormInterface
            && $form->isSubmitted()
            && !$form->isValid()
        ) {
            $status = 400;
        }

        $headers = [];
        if (
            method_exists($object, 'updatedAt')
            && ($updatedAt = $object->updatedAt()) instanceof DateTimeInterface
        ) {
            $headers['Last-Modified'] = $updatedAt->format('D, d M Y H:i:s \G\M\T');
        }

        $this->render(
            client: $client,
            view: $template,
            parameters: $parameters + $viewParameters,
            status: $status,
            headers: $headers,
            api: $api,
            cleanHtml: $cleanHtml ?? $this->defaultCleanHtml,
        );

        return $this;
    }
}

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

namespace Teknoo\East\WebsiteBundle\Recipe\Step;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait;

/**
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
     * @param FormInterface<mixed> $form
     */
    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        FormInterface $form,
        string $template,
        ObjectInterface $object,
        bool $isTranslatable = false
    ): RenderFormInterface {
        $viewParameters = $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);

        $this->render(
            $client,
            $template,
            [
                'objectInstance' => $object,
                'formView' => $form->createView(),
                'request' => $request,
                'isTranslatable' => $isTranslatable
            ]
            + $viewParameters
        );

        return $this;
    }
}

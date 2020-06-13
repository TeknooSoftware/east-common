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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\TemplatingTrait;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Object\PublishableInterface;
use Teknoo\East\Website\Object\SluggableInterface;
use Teknoo\East\Website\Service\DatesService;
use Teknoo\East\Website\Service\FindSlugService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminEditEndPoint implements RenderingInterface
{
    use TemplatingTrait;
    use AdminEndPointTrait;
    use AdminFormTrait;

    private DatesService $datesService;

    private FindSlugService $findSlugService;

    private string $slugField;

    public function setDatesService(DatesService $datesService): self
    {
        $this->datesService = $datesService;

        return $this;
    }

    public function setFindSlugService(FindSlugService $findSlugService, string $slugField): self
    {
        $this->findSlugService = $findSlugService;
        $this->slugField = $slugField;

        return $this;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $id,
        bool $isTranslatable = false,
        string $viewPath = null
    ): self {
        if (null === $viewPath) {
            $viewPath = $this->viewPath;
        }

        $this->loader->load(
            $id,
            new Promise(
                function (ObjectInterface $object) use ($client, $request, $isTranslatable, $viewPath) {
                    $parsedBody = (array) $request->getParsedBody();
                    if (
                        $object instanceof PublishableInterface
                        && isset($parsedBody['publish'])
                        && \is_callable([$object, 'setPublishedAt'])
                    ) {
                        $this->datesService->passMeTheDate([$object, 'setPublishedAt']);
                    }

                    $form = $this->createForm($object);
                    $form->handleRequest($request->getAttribute('request'));

                    if ($form->isSubmitted() && $form->isValid()) {
                        if ($object instanceof SluggableInterface) {
                            $object->prepareSlugNear(
                                $this->loader,
                                $this->findSlugService,
                                $this->slugField
                            );
                        }

                        $this->writer->save($object, new Promise(
                            function (ObjectInterface $object) use (
                                $client,
                                $form,
                                $request,
                                $isTranslatable,
                                $viewPath
                            ) {
                                //Recreate form to avoid error on dynamic form according to object.
                                $form = $this->createForm($object);

                                $this->render(
                                    $client,
                                    $viewPath,
                                    [
                                        'objectInstance' => $object,
                                        'formView' => $form->createView(),
                                        'request' => $request,
                                        'isTranslatable' => $isTranslatable
                                    ]
                                );
                            },
                            function ($error) use ($client) {
                                $client->errorInRequest($error);
                            }
                        ));

                        return;
                    }

                    $this->render(
                        $client,
                        $viewPath,
                        [
                            'objectInstance' => $object,
                            'formView' => $form->createView(),
                            'request' => $request,
                            'isTranslatable' => $isTranslatable
                        ]
                    );
                },
                function ($error) use ($client) {
                    $client->errorInRequest($error);
                }
            )
        );

        return $this;
    }
}

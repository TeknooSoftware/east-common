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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Object\PublishableInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminEditEndPoint implements EndPointInterface
{
    use EastEndPointTrait;
    use AdminEndPointTrait;
    use AdminFormTrait;

    private ?\DateTimeInterface $currentDate = null;

    public function setCurrentDate(\DateTimeInterface $currentDate): AdminEditEndPoint
    {
        if ($currentDate instanceof \DateTime) {
            $this->currentDate = \DateTimeImmutable::createFromMutable($currentDate);
        } else {
            $this->currentDate = $currentDate;
        }

        return $this;
    }

    private function getCurrentDateTime(): \DateTimeInterface
    {
        if (!$this->currentDate instanceof \DateTimeInterface) {
            $this->setCurrentDate(new \DateTime());
        }

        return $this->currentDate;
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
                    if ($object instanceof PublishableInterface && isset($request->getParsedBody()['publish'])) {
                        $object->setPublishedAt($this->getCurrentDateTime());
                    }

                    $form = $this->createForm($object);
                    $form->handleRequest($request->getAttribute('request'));

                    if ($form->isSubmitted() && $form->isValid()) {
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

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
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\AuthenticationTrait;
use Teknoo\East\FoundationBundle\EndPoint\RoutingTrait;
use Teknoo\East\FoundationBundle\EndPoint\TemplatingTrait;
use Teknoo\East\Website\Object\ObjectInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminNewEndPoint implements EndPointInterface
{
    use AuthenticationTrait;
    use RoutingTrait;
    use TemplatingTrait;
    use AdminEndPointTrait;
    use AdminFormTrait;

    private string $objectClass;

    public function setObjectClass(string $objectClass): self
    {
        if (!\class_exists($objectClass)) {
            throw new \LogicException("Error the object class $objectClass is not available");
        }

        $this->objectClass = $objectClass;

        return $this;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $editRoute,
        bool $isTranslatable = false,
        string $viewPath = null
    ): self {
        if (null === $viewPath) {
            $viewPath = $this->viewPath;
        }

        $class = $this->objectClass;

        $object = new $class();
        $form = $this->createForm($object);
        $form->handleRequest($request->getAttribute('request'));

        if ($form->isSubmitted() && $form->isValid()) {
            $this->writer->save($object, new Promise(
                function (ObjectInterface $object) use ($client, $editRoute) {
                    $this->redirectToRoute($client, $editRoute, ['id' => $object->getId()]);
                },
                function ($error) use ($client) {
                    $client->errorInRequest($error);
                }
            ));

            return $this;
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

        return $this;
    }
}
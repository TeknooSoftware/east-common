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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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
use Teknoo\East\Website\Object\SluggableInterface;
use Teknoo\East\Website\Service\FindSlugService;

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

    private FindSlugService $findSlugService;

    private string $slugField;

    private array $formOptions = [];

    public function setObjectClass(string $objectClass): self
    {
        if (!\class_exists($objectClass)) {
            throw new \LogicException("Error the object class $objectClass is not available");
        }

        $this->objectClass = $objectClass;

        return $this;
    }

    public function setFindSlugService(FindSlugService $findSlugService, string $slugField): self
    {
        $this->findSlugService = $findSlugService;
        $this->slugField = $slugField;

        return $this;
    }

    public function setFormOptions(array $formOptions): self
    {
        $this->formOptions = $formOptions;

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
        $form = $this->createForm($object, $this->formOptions);
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
                function (ObjectInterface $object) use ($client, $editRoute) {
                    $this->redirectToRoute($client, $editRoute, ['id' => $object->getId()]);
                },
                static function ($error) use ($client) {
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

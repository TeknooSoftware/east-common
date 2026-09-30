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

use DomainException;
use JsonException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\East\CommonBundle\Contracts\Form\FormManagerAwareInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;

use function in_array;
use function is_a;
use function is_array;
use function is_callable;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to create a form instance and handle the current request.
 * The form must be put into the manager's workplan.
 * If the key `publish` is present into the request, the current date will be passed to the object as published date.
 * In API mode, the key `publish` is also supported in a JSON body (for publishable objects, it is removed from values
 * submitted to the form). A malformed JSON body is reported as an error with the code 400.
 * Symfony implementation for `FormHandlingInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FormHandling implements FormHandlingInterface
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly FormFactoryInterface $formFactory
    ) {
    }

    /**
     * @param array<string, mixed> $options
     * @return FormInterface<mixed>
     */
    private function createForm(
        string $formClass,
        ObjectInterface $data,
        array $options = []
    ): FormInterface {
        return $this->formFactory->create(
            $formClass,
            $data,
            $options,
        );
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $formClass,
        ObjectInterface $object,
        array $formOptions = [],
        bool $formHandleRequest = true,
    ): FormHandlingInterface {
        $parsedBody = [];
        $jsonBody = [];

        //To avoid argument injection from HTTP request
        $api = $request->getAttribute('api', false);

        $isJsonBody = ['application/json'] === $request->getHeader('Content-Type');
        if (!$isJsonBody) {
            $parsedBody = (array) $request->getParsedBody();
        } elseif (!empty($api) && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            try {
                $jsonBody = (array) json_decode(
                    json: (string) $request->getBody(),
                    associative: true,
                    flags: JSON_THROW_ON_ERROR,
                );
            } catch (JsonException $error) {
                $manager->error(new DomainException('Malformed JSON body', 400, $error));

                return $this;
            }

            $parsedBody = $jsonBody;
            if ($object instanceof PublishableInterface) {
                unset($jsonBody['publish']);
            }
        }

        if (
            $object instanceof PublishableInterface
            && isset($parsedBody['publish'])
        ) {
            $this->datesService->passMeTheDate($object->setPublishedAt(...));
        }

        if (!empty($api)) {
            $formOptions['csrf_protection'] = false;

            if (is_a($formClass, FormApiAwareInterface::class, true)) {
                $formOptions['api'] = $api;
            }
        }

        if (is_a($formClass, FormManagerAwareInterface::class, true)) {
            $formOptions['manager'] = $manager;
        }

        $form = $this->createForm($formClass, $object, $formOptions);
        if (!empty($formHandleRequest)) {
            $attributes = $request->getAttributes();
            /** @var Request $sfRequest */
            $sfRequest = $request->getAttribute('request');

            if (
                'POST' !== $request->getMethod()
                || empty($attributes['_live_parameters'])
                || $sfRequest->request->has($form->getName())
            ) {
                $form->handleRequest($sfRequest);
            }

            if (
                !$form->isSubmitted()
                && empty($api)
                && !empty($liveBody = $request->getAttribute('_live_body', []))
                && is_array($liveBody)
                && is_array($liveBody['props'])
                && !empty($liveBody['props'][$form->getName()])
                && is_array($liveBody['props'][$form->getName()])
            ) {
                $form->submit($liveBody['props'][$form->getName()]);
            }

            if (
                !$form->isSubmitted()
                && !empty($api)
                && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])
            ) {
                $form->submit($jsonBody, false);
            }
        }

        $manager->updateWorkPlan([
            'form' => $form,
        ]);

        return $this;
    }
}

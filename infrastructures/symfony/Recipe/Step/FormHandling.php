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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\CommonBundle\Contracts\Form\FormApiAwareInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Time\DatesService;

use function in_array;
use function is_a;
use function is_callable;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to create a form instance and handle the current request.
 * The form must be put into the manager's workplan.
 * If the key `publish` is present into the request, the current date will be passed to the object as published date.
 * Symfony implementation for `FormHandlingInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
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

        //To avoid argument injection from HTTP request
        $api = $request->getAttribute('api', false);

        if (['application/json'] !== $request->getHeader('Content-Type')) {
            $parsedBody = (array) $request->getParsedBody();
        }

        if (
            $object instanceof PublishableInterface
            && isset($parsedBody['publish'])
            && is_callable([$object, 'setPublishedAt'])
        ) {
            $this->datesService->passMeTheDate($object->setPublishedAt(...));
        }

        if (!empty($api)) {
            $formOptions['csrf_protection'] = false;

            if (is_a($formClass, FormApiAwareInterface::class, true)) {
                $formOptions['api'] = $api;
            }
        }

        $form = $this->createForm($formClass, $object, $formOptions);
        if (!empty($formHandleRequest)) {
            $form->handleRequest($request->getAttribute('request'));

            if (
                !$form->isSubmitted()
                && !empty($api)
                && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])
            ) {
                $values = [];
                if (['application/json'] === $request->getHeader('Content-Type')) {
                    $values = (array) json_decode(
                        json: (string) $request->getBody(),
                        associative: true,
                        flags: JSON_THROW_ON_ERROR,
                    );
                }

                $form->submit($values, false);
            }
        }

        $manager->updateWorkPlan([
            'form' => $form,
        ]);

        return $this;
    }
}

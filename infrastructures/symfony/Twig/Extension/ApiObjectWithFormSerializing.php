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

namespace Teknoo\East\CommonBundle\Twig\Extension;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormView;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Twig\Attribute\AsTwigFilter;

/**
 * Twig filter `east_api_object_with_form_serialization` to serialize an object edited by a form into a JSON API
 * response. If the form has errors (errors of the root form view, including errors bubbled from its children), errors
 * are serialized instead of the object, with the envelope `{"meta": {"errors": true}, "data": {".field": "message"}}`.
 * Else the object is serialized like with the filter `east_api_object_serialization`.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiObjectWithFormSerializing
{
    public function __construct(
        private readonly ApiObjectSerializing $objectSerializing,
        private readonly FormError $formError,
    ) {
    }

    /**
     * @param object|array<mixed, mixed> $object
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     */
    #[AsTwigFilter(name: 'east_api_object_with_form_serialization', isSafe: ['html', 'json', 'js'])]
    public function rendering(
        object|array $object,
        ?FormView $formView,
        array $context = [],
        string $format = 'json',
        array $meta = [],
        ?IdentifiedObjectInterface $parentObject = null,
    ): string {
        if (
            isset($formView->vars['errors'])
            && $formView->vars['errors'] instanceof FormErrorIterator
            && $formView->vars['errors']->count() > 0
        ) {
            return $this->objectSerializing->serialize(
                object: $this->formError->getFieldErrors(
                    view: $formView,
                ),
                context: [],
                format: $format,
                meta: [
                    'errors' => true,
                ],
            );
        }

        return $this->objectSerializing->serialize(
            object: $object,
            context: $context,
            format: $format,
            meta: $meta,
            parentObject: $parentObject,
        );
    }
}

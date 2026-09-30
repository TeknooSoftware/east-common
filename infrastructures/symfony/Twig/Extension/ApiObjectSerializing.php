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

use Symfony\Component\Serializer\SerializerInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\CommonBundle\Twig\Extension\Exception\MissingSerializerException;
use Twig\Attribute\AsTwigFilter;

use function array_merge;
use function get_parent_class;

/**
 * Twig filter `east_api_object_serialization` to serialize an object (or an array) into a JSON API response, with the
 * envelope `{"meta": {...}, "data": ...}`. When the object (or the parent object passed as argument) is an identified
 * object, its id and its root class name are added into the `meta` part. Extra meta can be passed as argument.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiObjectSerializing
{
    public function __construct(
        private readonly ?SerializerInterface $serializer = null,
    ) {
    }

    /**
     * @param object|array<mixed, mixed> $object
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     */
    #[AsTwigFilter(name: 'east_api_object_serialization', isSafe: ['html', 'json', 'js'])]
    public function serialize(
        object|array $object,
        array $context = [],
        string $format = 'json',
        array $meta = [],
        ?IdentifiedObjectInterface $parentObject = null,
    ): string {
        if (null === $this->serializer) {
            throw new MissingSerializerException(
                'The Symfony Serializer is required to render JSON API responses, '
                . 'install `symfony/serializer` and enable it',
            );
        }

        $computedMeta = [];

        $metaObject = $object;
        if (!$metaObject instanceof IdentifiedObjectInterface && null !== $parentObject) {
            $metaObject = $parentObject;
        }

        if ($metaObject instanceof IdentifiedObjectInterface) {
            $rootClass = $metaObject::class;
            while (false !== ($parentClass = get_parent_class($rootClass))) {
                $rootClass = $parentClass;
            }

            $computedMeta['id'] = $metaObject->getId();
            $computedMeta['@class'] = $rootClass;
        }

        return $this->serializer->serialize(
            data: [
                'meta' => array_merge($computedMeta, $meta),
                'data' => $object,
            ],
            format: $format,
            context: $context,
        );
    }
}

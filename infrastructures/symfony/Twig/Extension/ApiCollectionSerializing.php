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

use Countable;
use Symfony\Component\Serializer\SerializerInterface;
use Teknoo\East\CommonBundle\Twig\Extension\Exception\MissingSerializerException;
use Traversable;
use Twig\Attribute\AsTwigFilter;

use function array_merge;
use function array_values;
use function count;
use function iterator_to_array;

/**
 * Twig filter `east_api_collection_serialization` to serialize a (paginated) collection of objects into a JSON API
 * response, with the envelope `{"meta": {"totalPages": ..., "page": ..., "count": ...}, "data": [...]}`.
 * `count` is the value returned by the collection if it is countable (the total of available elements for paginated
 * queries), else the number of elements in the collection. `data` is always a JSON list.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiCollectionSerializing
{
    public function __construct(
        private readonly ?SerializerInterface $serializer = null,
    ) {
    }

    /**
     * @param iterable<mixed> $collection
     * @param array<string, mixed> $context
     * @param array<string, mixed> $meta
     */
    #[AsTwigFilter(name: 'east_api_collection_serialization', isSafe: ['html', 'json', 'js'])]
    public function serialize(
        iterable $collection,
        int $currentPage,
        int $countPages,
        array $context = [],
        string $format = 'json',
        array $meta = [],
    ): string {
        if (null === $this->serializer) {
            throw new MissingSerializerException(
                'The Symfony Serializer is required to render JSON API responses, '
                . 'install `symfony/serializer` and enable it',
            );
        }

        if ($collection instanceof Traversable) {
            $items = iterator_to_array($collection, false);
        } else {
            $items = array_values($collection);
        }

        if ($collection instanceof Countable) {
            $count = $collection->count();
        } else {
            $count = count($items);
        }

        return $this->serializer->serialize(
            data: [
                'meta' => array_merge(
                    [
                        'totalPages' => $countPages,
                        'page' => $currentPage,
                        'count' => $count,
                    ],
                    $meta,
                ),
                'data' => $items,
            ],
            format: $format,
            context: $context,
        );
    }
}

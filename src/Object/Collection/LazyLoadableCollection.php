<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Object\Collection;

use IteratorAggregate;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Query\Exception\NonFetchedCollectionException;
use Teknoo\Recipe\Promise\Promise;
use Throwable;
use Traversable;

/**
 * Collection of objects to fetch only at first iteration, on demand
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template ObjectClassInCollection
 * @implements IteratorAggregate<ObjectClassInCollection>
 */
class LazyLoadableCollection implements IteratorAggregate
{
    /**
     * @var iterable<ObjectClassInCollection>|null
     */
    private ?iterable $fetchedCollection = null;

    /**
     * @param LoaderInterface<ObjectClassInCollection> $loader
     * @param QueryCollectionInterface<ObjectClassInCollection> $query
     */
    public function __construct(
        private LoaderInterface $loader,
        private QueryCollectionInterface $query,
    ) {
    }

    /**
     * @return LazyLoadableCollection<ObjectClassInCollection>
     */
    public function fetchCollection(): self
    {
        /** @var Promise<iterable<ObjectClassInCollection>, mixed, iterable<ObjectClassInCollection>> $promise */
        $promise = new Promise(
            fn (iterable $collection) => $this->fetchedCollection = $collection,
            fn (Throwable $error) => throw $error,
        );

        $this->loader->query(
            $this->query,
            $promise,
        );

        return $this;
    }

    /**
     * @return Traversable<ObjectClassInCollection>
     * @throws NonFetchedCollectionException
     */
    public function getIterator(): Traversable
    {
        if (null === $this->fetchedCollection) {
            $this->fetchCollection();
        }

        if (null === $this->fetchedCollection) {
            throw new NonFetchedCollectionException('Error no collection fetched');
        }

        yield from $this->fetchedCollection;
    }
}

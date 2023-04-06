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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\Loader;

use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface defining methods to implement in loader in charge of persisted objects, to load of find via queries,
 * objects to be used into recipes of this library.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 */
interface LoaderInterface
{
    /**
     * @param PromiseInterface<TSuccessArgType, mixed> $promise
     * @return LoaderInterface<TSuccessArgType>
     */
    public function load(string $id, PromiseInterface $promise): LoaderInterface;

    /**
     * @param QueryElementInterface<TSuccessArgType> $query
     * @param PromiseInterface<TSuccessArgType, mixed> $promise
     * @return LoaderInterface<TSuccessArgType>
     */
    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface;

    /**
     * @param QueryCollectionInterface<TSuccessArgType> $query
     * @param PromiseInterface<iterable<TSuccessArgType>, mixed> $promise
     * @return LoaderInterface<TSuccessArgType>
     */
    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface;
}

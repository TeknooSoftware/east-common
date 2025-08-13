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

namespace Teknoo\East\Common\Contracts\Query;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define query to fetch persisted objects from a database.
 * Each query must be implemented into a specific class.
 * These query must return a single instance of an object or a scalar value
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template  TSuccessArgType of ObjectInterface
 */
interface QueryElementInterface extends QueryInterface
{
    /**
     * @param LoaderInterface<TSuccessArgType> $loader
     * @param RepositoryInterface<TSuccessArgType> $repository
     * @param PromiseInterface<TSuccessArgType, mixed> $promise
     * @return QueryElementInterface<TSuccessArgType>
     */
    public function fetch(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryElementInterface;
}

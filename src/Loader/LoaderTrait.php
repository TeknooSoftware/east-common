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

namespace Teknoo\East\Common\Loader;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Trait to share standard implementation of load and query methods of loaders
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 */
trait LoaderTrait
{
    /**
     * @var \Teknoo\East\Common\Contracts\DBSource\RepositoryInterface<TSuccessArgType>
     */
    protected RepositoryInterface $repository;

    /**
     * @return \Teknoo\East\Common\Contracts\DBSource\RepositoryInterface<TSuccessArgType>
     */
    protected function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        $criteria = [
            'id' => $id,
            'deletedAt' => null
        ];

        try {
            $this->getRepository()->findOneBy($criteria, $promise);
        } catch (Throwable $exception) {
            $promise->fail($exception);
        }

        return $this;
    }

    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->fetch($this, $this->getRepository(), $promise);

        return $this;
    }

    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->execute($this, $this->getRepository(), $promise);

        return $this;
    }
}

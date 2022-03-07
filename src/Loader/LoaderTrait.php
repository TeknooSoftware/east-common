<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Loader;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Query\QueryInterface;
use Throwable;

/**
 * Trait to share standard implementation of load and query methods of loaders
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @mixin LoaderInterface
 */
trait LoaderTrait
{
    protected RepositoryInterface $repository;

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

    public function query(QueryInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->execute($this, $this->getRepository(), $promise);

        return $this;
    }
}

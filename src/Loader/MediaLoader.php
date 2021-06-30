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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Loader;

use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Query\Expr\InclusiveOr;
use Teknoo\East\Website\Query\QueryInterface;
use Throwable;

/**
 * Object loader in charge of object `Teknoo\East\Website\Object\Media`.
 * Must provide an implementation of `MediaRepositoryInterface` to be able work.
 * The loader's load method is compliant with legacy id, migrated into metadata or new id format, automatically.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaLoader implements LoaderInterface
{
    private RepositoryInterface $repository;

    public function __construct(MediaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function load(string $id, PromiseInterface $promise): LoaderInterface
    {
        $criteria = [
            new InclusiveOr(
                ['id' => $id,],
                ['metadata.legacyId' => $id,]
            )
        ];

        try {
            $this->repository->findOneBy($criteria, $promise);
        } catch (Throwable $exception) {
            $promise->fail($exception);
        }

        return $this;
    }

    public function query(QueryInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->execute($this, $this->repository, $promise);

        return $this;
    }
}

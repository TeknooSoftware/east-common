<?php

/*
 * East Website.
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
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Loader;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Object loader in charge of object `Teknoo\East\Common\Object\Media`.
 * Must provide an implementation of `MediaRepositoryInterface` to be able work.
 * The loader's load method is compliant with legacy id, migrated into metadata or new id format, automatically.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements LoaderInterface<Media>
 */
class MediaLoader implements LoaderInterface
{
    /**
     * @var RepositoryInterface<Media>
     */
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

    public function fetch(QueryElementInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->fetch($this, $this->repository, $promise);

        return $this;
    }

    public function query(QueryCollectionInterface $query, PromiseInterface $promise): LoaderInterface
    {
        $query->execute($this, $this->repository, $promise);

        return $this;
    }
}

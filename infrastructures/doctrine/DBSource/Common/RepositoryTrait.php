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

namespace Teknoo\East\Website\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectRepository;
use DomainException;
use RuntimeException;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Throwable;

/**
 * Default repository implementation of generic doctrine repositories.
 * Usable with ORM or ODM, but a optimized version dedicated to ODM is available into `ODM`
 * namespace.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait RepositoryTrait
{
    use ExprConversionTrait;

    /**
     * @param ObjectRepository<mixed> $repository
     */
    public function __construct(
        private ObjectRepository $repository,
    ) {
    }

    public function find(string $id, PromiseInterface $promise): RepositoryInterface
    {
        $result = $this->repository->find($id);

        if (!empty($result)) {
            $promise->success($result);
        } else {
            $promise->fail(new DomainException('Object not found'));
        }

        return $this;
    }

    public function findAll(PromiseInterface $promise): RepositoryInterface
    {
        $promise->success($this->repository->findAll());

        return $this;
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, 'asc'|'ASC'|'desc'|'DESC'>|null $orderBy
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): RepositoryInterface {
        $promise->success($this->repository->findBy($criteria, $orderBy, $limit, $offset));

        return $this;
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function count(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        $promise->fail(new RuntimeException('Error, this method is not available with this repository'));

        return $this;
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function findOneBy(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        $error = null;
        try {
            $result = $this->repository->findOneBy(static::convert($criteria));

            if (!empty($result)) {
                $promise->success($result);
            } else {
                $error = new DomainException('Object not found');
            }
        } catch (Throwable $catchedError) {
            $error = $catchedError;
        } finally {
            if (null !== $error) {
                $promise->fail($error);
            }
        }

        return $this;
    }
}

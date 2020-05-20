<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\DBSource;

use Doctrine\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait RepositoryTrait
{
    private ObjectRepository $repository;

    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id, PromiseInterface $promise): RepositoryInterface
    {
        $result = $this->repository->find($id);

        if (!empty($result)) {
            $promise->success($result);
        } else {
            $promise->fail(new \DomainException('Object not found'));
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(PromiseInterface $promise): RepositoryInterface
    {
        $promise->success($this->repository->findAll());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): RepositoryInterface {
        if (!$this->repository instanceof DocumentRepository) {
            $promise->success($this->repository->findBy($criteria, $orderBy, $limit, $offset));

            return $this;
        }

        $queryBuilder = $this->repository->createQueryBuilder();

        $queryBuilder->equals($criteria);
        if (!empty($orderBy)) {
            $queryBuilder->sort($orderBy);
        }

        if (!empty($limit)) {
            $queryBuilder->limit($limit);
        }

        if (!empty($offset)) {
            $queryBuilder->skip($offset);
        }

        $query = $queryBuilder->getQuery();

        $promise->success($query->execute());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        try {
            $result = $this->repository->findOneBy($criteria);

            if (!empty($result)) {
                $promise->success($result);
            } else {
                $promise->fail(new \DomainException('Object not found'));
            }
        } catch (\Throwable $error) {
            $promise->fail($error);
        }

        return $this;
    }
}

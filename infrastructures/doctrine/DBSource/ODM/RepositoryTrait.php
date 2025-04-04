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

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use DomainException;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * ODM optimized repository implementation of ODM doctrine repositories.
 * Can be used only with Doctrine ODM.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template ObjectClass
 */
trait RepositoryTrait
{
    use ExprConversionTrait;

    /**
     * @param DocumentRepository<ObjectClass> $repository
     */
    public function __construct(
        private DocumentRepository $repository
    ) {
    }

    /**
     * @param string[] $hydrate list of fields hosting a reference to hydrate
     */
    private function primeReference(Builder $qb, array $hydrate = [],): void
    {
        foreach ($hydrate as $fieldName) {
            $qb->field($fieldName)->prime(true);
        }
    }

    public function find(
        string $id,
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface {
        $qb = $this->repository->createQueryBuilder();
        $qb->equals(['_id' => $id]);

        $this->primeReference($qb, $hydrate);

        $query = $qb->getQuery();

        /** @var ObjectClass|null $result */
        $result = $query->getSingleResult();

        if (!empty($result)) {
            $promise->success($result);
        } else {
            $promise->fail(new DomainException('Object not found', 404));
        }

        return $this;
    }

    public function findAll(
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface {
        $queryBuilder = $this->repository->createQueryBuilder();

        $this->primeReference($queryBuilder, $hydrate);

        $query = $queryBuilder->getQuery();

        /** @var ObjectClass[] $result */
        $result = $query->execute();
        $promise->success($result);

        return $this;
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, Direction>|null $orderBy
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        array $hydrate = [],
    ): RepositoryInterface {
        $queryBuilder = $this->repository->createQueryBuilder();

        $queryBuilder->equals(self::convert($criteria));

        $this->primeReference($queryBuilder, $hydrate);

        if (!empty($orderBy)) {
            array_walk(
                $orderBy,
                static fn(Direction &$item): string => $item = $item->value
            );

            $queryBuilder->sort($orderBy);
        }

        if (!empty($limit)) {
            $queryBuilder->limit($limit);
        }

        if (!empty($offset)) {
            $queryBuilder->skip($offset);
        }

        $query = $queryBuilder->getQuery();

        /** @var iterable<ObjectClass> $result */
        $result = $query->execute();
        $promise->success($result);

        return $this;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function distinctBy(
        string $field,
        array $criteria,
        PromiseInterface $promise,
        ?int $limit = null,
        ?int $offset = null,
    ): RepositoryInterface {
        $queryBuilder = $this->repository->createQueryBuilder();

        $queryBuilder->equals(self::convert($criteria));

        $queryBuilder->distinct($field);

        if (!empty($limit)) {
            $queryBuilder->limit($limit);
        }

        if (!empty($offset)) {
            $queryBuilder->skip($offset);
        }

        $query = $queryBuilder->getQuery();

        /** @var iterable<mixed> $result */
        $result = $query->execute();
        $promise->success($result);

        return $this;
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function count(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        $queryBuilder = $this->repository->createQueryBuilder();

        $queryBuilder->equals(self::convert($criteria));

        $queryBuilder->count();

        $query = $queryBuilder->getQuery();

        /** @var int $result */
        $result = $query->execute();
        $promise->success($result);

        return $this;
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function findOneBy(
        array $criteria,
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface {
        $error = null;
        try {
            $queryBuilder = $this->repository->createQueryBuilder();

            $queryBuilder->equals(self::convert($criteria));

            $this->primeReference($queryBuilder, $hydrate);

            $queryBuilder->limit(1);

            $query = $queryBuilder->getQuery();

            /** @var ObjectClass|null $result */
            $result = $query->getSingleResult();

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

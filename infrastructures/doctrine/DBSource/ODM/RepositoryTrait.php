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

namespace Teknoo\East\Website\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use DomainException;
use Teknoo\East\Website\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Throwable;

/**
 * ODM optimized repository implementation of ODM doctrine repositories.
 * Can be used only with Doctrine ODM.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
     * @param array<string, Direction>|null $orderBy
     */
    public function findBy(
        array $criteria,
        PromiseInterface $promise,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): RepositoryInterface {
        $queryBuilder = $this->repository->createQueryBuilder();

        $queryBuilder->equals(self::convert($criteria));

        if (!empty($orderBy)) {
            array_walk(
                $orderBy,
                fn(Direction &$item) => $item = $item->value
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
    public function findOneBy(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        $error = null;
        try {
            $result = $this->repository->findOneBy(self::convert($criteria));

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

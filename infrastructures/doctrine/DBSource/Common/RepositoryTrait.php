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

namespace Teknoo\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectRepository;
use DomainException;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function array_walk;
use function trigger_error;

use const E_USER_NOTICE;

/**
 * Default repository implementation of generic doctrine repositories.
 * Usable with ORM or ODM, but a optimized version dedicated to ODM is available into `ODM`
 * namespace.
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
     * @param ObjectRepository<ObjectClass> $repository
     */
    public function __construct(
        private ObjectRepository $repository,
    ) {
    }

    public function find(
        string $id,
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface {
        /** @var ObjectClass|null $result */
        $result = $this->repository->find($id);

        if (!empty($result)) {
            $promise->success($result);
        } else {
            $promise->fail(new DomainException('Object not found', 404));
        }

        if (!empty($hydrate)) {
            trigger_error('$hydrate behavior is not available with common doctrine implementation', E_USER_NOTICE);
        }

        return $this;
    }

    public function findAll(
        PromiseInterface $promise,
        array $hydrate = [],
    ): RepositoryInterface {
        /** @var iterable<ObjectClass> $result */
        $result = $this->repository->findAll();
        $promise->success($result);

        if (!empty($hydrate)) {
            trigger_error('$hydrate behavior is not available with common doctrine implementation', E_USER_NOTICE);
        }

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
        if (null !== $orderBy) {
            array_walk(
                $orderBy,
                static fn(Direction &$item) => $item = $item->value
            );
        }

        /** @var array<string, 'asc'|'ASC'|'desc'|'DESC'>|null $orderBy */
        /** @var iterable<ObjectClass> $result */
        $result = $this->repository->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        $promise->success($result);

        if (!empty($hydrate)) {
            trigger_error('$hydrate behavior is not available with common doctrine implementation', E_USER_NOTICE);
        }

        return $this;
    }

    /**
     * @param array<int|string, mixed> $criteria
     */
    public function count(array $criteria, PromiseInterface $promise): RepositoryInterface
    {
        $promise->fail(
            new RuntimeException('Error, this method is not available with this repository', 500)
        );

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
            /** @var ObjectClass|null $result */
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

        if (!empty($hydrate)) {
            trigger_error('$hydrate behavior is not available with common doctrine implementation', E_USER_NOTICE);
        }

        return $this;
    }
}

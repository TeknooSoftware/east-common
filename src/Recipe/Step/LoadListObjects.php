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

namespace Teknoo\East\Common\Recipe\Step;

use Countable;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Query\Exception\LoadingException;
use Teknoo\East\Common\Query\Exception\WrongCriteriaException;
use Teknoo\East\Common\Query\PaginationQuery;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function ceil;
use function is_array;
use function is_object;
use function is_string;
use function preg_match;

/**
 * Generic step recipe to load a liste of persisted objects thank to its loader and the PaginationQuery and put it
 * into the workplan at key 'objectsCollection'.
 * Criteria are sanitized before to be passed to the query (accept only `^[a-zA-Z0-9\-_]+` keys and only
 * `^[\p{Sm}\p{Sc}\p{L}\p{N}\p{Z}_\-\.\@,]+` string, or a ExprInterface instance, or an array following this same rules
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadListObjects
{
    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    private function sanitizeCriteria(array &$criteria): array
    {
        $final = [];
        foreach ($criteria as $key => &$value) {
            if (
                !is_string($key)
                || 0 === preg_match('#^[a-zA-Z0-9\-_]+$#S', $key)
            ) {
                throw new WrongCriteriaException('Wrong key in criteria, must follow [a-zA-Z0-9\-_]+', 400);
            }

            if (
                is_object($value)
                && !$value instanceof ExprInterface
            ) {
                throw new WrongCriteriaException("Wrong value in criteria for $key", 400);
            }

            if (is_array($value)) {
                $value = $this->sanitizeCriteria($value);
            } elseif (
                is_string($value)
                && 0 === preg_match("#^[\p{Sm}\p{Sc}\p{L}\p{N}\p{Z}_\-\.\@,%*\#\!;\/\(\)]+$#uS", $value)
            ) {
                throw new WrongCriteriaException("Wrong value in criteria for $key", 400);
            }

            $final[$key] = $value;
        }

        return $final;
    }

    /**
     * @param LoaderInterface<ObjectInterface> $loader
     * @param array<string, Direction> $order
     * @param array<string, string> $criteria
     */
    public function __invoke(
        LoaderInterface $loader,
        ManagerInterface $manager,
        array $order,
        int $itemsPerPage,
        int $page,
        array $criteria = [],
        string $errorMessage = 'Error while executing select query',
        int $errorCode = 500,
    ): self {
        try {
            $criteria = $this->sanitizeCriteria($criteria);
        } catch (Throwable $error) {
            $manager->error($error);

            return $this;
        }

        /** @var Promise<iterable<ObjectInterface>, mixed, mixed> $executePromise */
        $executePromise = new Promise(
            static function ($objects) use ($itemsPerPage, $manager): void {
                $pageCount = 1;
                if ($objects instanceof Countable) {
                    $pageCount = (int) ceil($objects->count() / $itemsPerPage);
                }

                $manager->updateWorkPlan(
                    [
                        'objectsCollection' => $objects,
                        'pageCount' => $pageCount,
                    ],
                );
            },
            static fn (#[SensitiveParameter] Throwable $throwable): ChefInterface => $manager->error(
                new LoadingException(
                    message: $errorMessage,
                    code: $errorCode,
                    previous: $throwable,
                )
            ),
        );

        $loader->query(
            new PaginationQuery(
                $criteria,
                $order,
                $itemsPerPage,
                ($page - 1) * $itemsPerPage,
            ),
            $executePromise,
        );

        return $this;
    }
}

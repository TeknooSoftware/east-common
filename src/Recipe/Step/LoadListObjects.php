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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\Expr\ExprInterface;
use Teknoo\East\Website\Query\PaginationQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
                !\is_string($key)
                || 0 === \preg_match('#^[a-zA-Z0-9\-_]+$#S', $key)
            ) {
                throw new \RuntimeException('Wrong key in criteria, must follow [a-zA-Z0-9\-_]+', 400);
            }

            if (
                \is_object($value)
                && !$value instanceof ExprInterface
            ) {
                throw new \RuntimeException("Wrong value in criteria for $key", 400);
            }

            if (\is_array($value)) {
                $value = $this->sanitizeCriteria($value);
            } elseif (
                \is_string($value)
                && 0 === \preg_match("#^[\p{Sm}\p{Sc}\p{L}\p{N}\p{Z}_\-\.\@,]+$#uS", $value)
            ) {
                throw new \RuntimeException("Wrong value in criteria for $key", 400);
            }

            $final[$key] = $value;
        }

        return $final;
    }

    /**
     * @param array<string, string> $criteria
     */
    public function __invoke(
        LoaderInterface $loader,
        ManagerInterface $manager,
        array $order,
        int $itemsPerPage,
        int $page,
        array $criteria = []
    ): self {
        try {
            $criteria = $this->sanitizeCriteria($criteria);
        } catch (\Throwable $error) {
            $manager->error($error);

            return $this;
        }

        $loader->query(
            new PaginationQuery(
                $criteria,
                $order,
                $itemsPerPage,
                ($page - 1) * $itemsPerPage
            ),
            new Promise(
                static function ($objects) use ($itemsPerPage, $manager) {
                    $pageCount = 1;
                    if ($objects instanceof \Countable) {
                        $pageCount = (int) \ceil($objects->count() / $itemsPerPage);
                    }

                    $manager->updateWorkPlan(
                        [
                            'objectsCollection' => $objects,
                            'pageCount' => $pageCount
                        ]
                    );
                },
                [$manager, 'error']
            )
        );

        return $this;
    }
}

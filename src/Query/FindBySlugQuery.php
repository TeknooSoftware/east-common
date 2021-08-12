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

namespace Teknoo\East\Website\Query;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Generic class implementing query to load any sluggable instance from its slug, and pass result to the
 * passed promise.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FindBySlugQuery implements QueryInterface, ImmutableInterface
{
    use ImmutableTrait;

    private string $slugField;

    private string $slugValue;

    private bool $includeDeleted;

    public function __construct(string $slugField, string $slugValue, bool $includeDeleted = false)
    {
        $this->uniqueConstructorCheck();

        $this->slugField = $slugField;
        $this->slugValue = $slugValue;
        $this->includeDeleted = $includeDeleted;
    }

    public function execute(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryInterface {
        $filters = [$this->slugField => $this->slugValue];

        if (!$this->includeDeleted) {
            $filters['deletedAt'] = null;
        }

        $repository->findOneBy(
            $filters,
            $promise
        );

        return $this;
    }
}

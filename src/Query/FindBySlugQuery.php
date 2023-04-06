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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Query;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Generic class implementing query to load any sluggable instance from its slug, and pass result to the
 * passed promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements QueryElementInterface<SluggableInterface<IdentifiedObjectInterface>>
 */
class FindBySlugQuery implements QueryElementInterface, ImmutableInterface
{
    use ImmutableTrait;

    public function __construct(
        private readonly string $slugField,
        private readonly string $slugValue,
        private readonly bool $includeDeleted = false,
        private readonly ?IdentifiedObjectInterface $sluggable = null
    ) {
        $this->uniqueConstructorCheck();
    }

    public function fetch(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryElementInterface {
        $filters = [$this->slugField => $this->slugValue];

        if (!$this->includeDeleted) {
            $filters['deletedAt'] = null;
        }

        if (
            $this->sluggable instanceof IdentifiedObjectInterface
            && !empty($id = $this->sluggable->getId())
        ) {
            $filters['id'] = new NotEqual($id);
        }

        $repository->findOneBy(
            $filters,
            $promise
        );

        return $this;
    }
}

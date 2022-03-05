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

namespace Teknoo\East\Website\Query\Item;

use Teknoo\East\Website\Query\Enum\Direction;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\QueryInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Class implementing query to load first level items about a menu, ordered by their position (ascendant order)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class TopItemByLocationQuery implements QueryInterface, ImmutableInterface
{
    use ImmutableTrait;

    public function __construct(
        private readonly string $location,
    ) {
        $this->uniqueConstructorCheck();
    }

    public function execute(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryInterface {
        $repository->findBy(
            [
                'location' => $this->location,
                'deletedAt' => null,
            ],
            $promise,
            [
                'parent' => Direction::Asc,
                'position' => Direction::Asc,
            ]
        );

        return $this;
    }
}

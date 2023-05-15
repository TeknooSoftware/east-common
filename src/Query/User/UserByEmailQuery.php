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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Query\User;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Object\User;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Class implementing query to load a non deleted user from its email, and pass result to the
 * passed promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements QueryElementInterface<User>
 */
class UserByEmailQuery implements QueryElementInterface, ImmutableInterface
{
    use ImmutableTrait;

    public function __construct(
        private readonly string $email,
    ) {
        $this->uniqueConstructorCheck();
    }

    public function fetch(
        LoaderInterface $loader,
        RepositoryInterface $repository,
        PromiseInterface $promise
    ): QueryElementInterface {
        $repository->findOneBy(
            [
                'email' => $this->email,
                'active' => new InclusiveOr(
                    ['active' => true],
                    ['active' => null],
                ),
                'deletedAt' => null
            ],
            $promise
        );

        return $this;
    }
}

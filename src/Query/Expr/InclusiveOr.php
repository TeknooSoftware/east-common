<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Query\Expr;

use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Immutable\ImmutableTrait;

/**
 * Operator to add a meta-constraint to require at least once sub constraint is valided to be validated (aka the
 * "OR" operator in SQL)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class InclusiveOr implements ExprInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * @var array<int|string, mixed>
     */
    private readonly array $values;

    /**
     * @param array<int|string, mixed> $values
     */
    public function __construct(array ...$values)
    {
        $this->uniqueConstructorCheck();

        $this->values = $values;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

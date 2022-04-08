<?php

/*
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class InclusiveOr implements ExprInterface, ImmutableInterface
{
    use ImmutableTrait;

    /**
     * @var array<string|int, mixed>
     */
    private readonly array $values;

    /**.
     * @param array<string|int, mixed> $values
     */
    public function __construct(array ...$values)
    {
        $this->uniqueConstructorCheck();

        $this->values = $values;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}

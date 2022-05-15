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

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\ExprConversionTrait as CommonTrait;
use Teknoo\East\Common\Query\Expr\In;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\Expr\NotIn;
use Teknoo\East\Common\Query\Expr\ObjectReference;

use function is_array;

/**
 * Odm Optimised implementation dedicated to ODM Doctrine component to convert East Common
 * Queries expressions to MongoDB queries.
 * Can be used only with Doctrine ODM.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait ExprConversionTrait
{
    use CommonTrait;

    /**
     * @param array<string, mixed> $final
     * @param NotEqual $expr
     */
    private static function processNotEqualExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $values = $expr->getValue();
        if (is_array($values)) {
            $values = self::convert($values);
        }

        $final['$ne'] = [$key => $values];
    }

    /**
     * @param array<string, mixed> $final
     * @param In $expr
     */
    private static function processInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = ['$in' => $expr->getValues()];
    }

    /**
     * @param array<string, mixed> $final
     * @param NotIn $expr
     */
    private static function processNotInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = ['$nin' => $expr->getValues()];
    }

    /**
     * @param array<string, mixed> $final
     * @param InclusiveOr $expr
     */
    private static function processInclusiveOrExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $orValues = $expr->getValues();
        $final['$or'] = self::convert($orValues);
    }

    /**
     * @param array<string, mixed> $final
     * @param ObjectReference $expr
     */
    private static function processObjectReferenceExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key . '.$id'] = $expr->getObject()->getId();
    }
}

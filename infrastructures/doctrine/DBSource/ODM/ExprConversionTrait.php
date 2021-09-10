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

namespace Teknoo\East\Website\Doctrine\DBSource\ODM;

use Teknoo\East\Website\Doctrine\DBSource\Common\ExprConversionTrait as CommonTrait;
use Teknoo\East\Website\Query\Expr\ExprInterface;
use Teknoo\East\Website\Query\Expr\In;
use Teknoo\East\Website\Query\Expr\InclusiveOr;
use Teknoo\East\Website\Query\Expr\ObjectReference;

/**
 * Odm Optimised implementation dedicated to ODM Doctrine component to convert East Website
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
     * @param In $expr
     */
    private static function processInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = ['$in' => $expr->getValues()];
    }

    /**
     * @param array<string, mixed> $final
     * @param InclusiveOr $expr
     */
    private static function processInclusiveOrExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $orValues = $expr->getValues();
        $final['$or'] = static::convert($orValues);
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

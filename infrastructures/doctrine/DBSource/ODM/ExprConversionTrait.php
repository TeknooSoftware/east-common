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

namespace Teknoo\East\Common\Doctrine\DBSource\ODM;

use MongoDB\BSON\Regex as MongoRegex;
use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Doctrine\DBSource\Common\ExprConversionTrait as CommonTrait;
use Teknoo\East\Common\Query\Expr\Greater;
use Teknoo\East\Common\Query\Expr\In;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\Lower;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\Expr\NotIn;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Common\Query\Expr\Regex;
use Teknoo\East\Common\Query\Expr\StrictlyGreater;
use Teknoo\East\Common\Query\Expr\StrictlyLower;

use function is_array;

/**
 * Odm Optimised implementation dedicated to ODM Doctrine component to convert East Common
 * Queries expressions to MongoDB queries.
 * Can be used only with Doctrine ODM.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

        $final['$ne'] = ($final['$ne'] ?? []) + [$key => $values];
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param Greater $expr
     */
    private static function processGreaterExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$gte'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param Lower $expr
     */
    private static function processLowerExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$lte'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param StrictlyGreater $expr
     */
    private static function processStrictlyGreaterExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$gt'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param StrictlyLower $expr
     */
    private static function processStrictlyLowerExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$lt'] = $expr->getValue();
    }

    /**
     * @param array<string, mixed> $final
     * @param Regex $expr$final[$key] = ['$nin' => $expr->getValues()];
     */
    private static function processRegexExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = new MongoRegex(
            pattern: $expr->getPattern(),
            flags: $expr->getOptions(),
        );
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param In $expr
     */
    private static function processInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$in'] = $expr->getValues();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param NotIn $expr
     */
    private static function processNotInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['$nin'] = $expr->getValues();
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

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

namespace Teknoo\East\Common\Doctrine\DBSource\Common;

use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Doctrine\DBSource\Exception\NonManagedClassException;
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
 * Default implementation dedicated to generic Doctrine component to convert East Common
 * Queries expressions to Doctrines generic queries.
 * Usable with ORM or ODM, but a optimized version dedicated to ODM is available into `ODM`
 * namespace.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ExprConversionTrait
{
    //Can not use self::processXXX(...)
    /**
     * @var array<string, callable>
     */
    private static $exprMappings = [
        In::class => [self::class, 'processInExpr'],
        InclusiveOr::class => [self::class, 'processInclusiveOrExpr'],
        NotEqual::class => [self::class, 'processNotEqualExpr'],
        Greater::class => [self::class, 'processGreaterExpr'],
        Lower::class => [self::class, 'processLowerExpr'],
        StrictlyGreater::class => [self::class, 'processStrictlyGreaterExpr'],
        StrictlyLower::class => [self::class, 'processStrictlyLowerExpr'],
        NotIn::class => [self::class, 'processNotInExpr'],
        ObjectReference::class => [self::class, 'processObjectReferenceExpr'],
        Regex::class => [self::class, 'processRegexExpr'],
    ];

    public static function addExprMappingConversion(string $exprClass, callable $conversionFunction): void
    {
        self::$exprMappings[$exprClass] = $conversionFunction;
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param NotEqual $expr
     */
    private static function processNotEqualExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final['notEqual'] = ($final['notEqual'] ?? []) + [$key => $expr->getValue()];
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param Greater $expr
     */
    private static function processGreaterExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['gte'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param Lower $expr
     */
    private static function processLowerExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['lte'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param StrictlyGreater $expr
     */
    private static function processStrictlyGreaterExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['gt'] = $expr->getValue();
    }

    /**
     * @param array<string, array<string, mixed>> $final
     * @param StrictlyLower $expr
     */
    private static function processStrictlyLowerExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key]['lt'] = $expr->getValue();
    }

    /**
     * @param array<string, mixed> $final
     * @param Regex $expr
     */
    private static function processRegexExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = "/{$expr->getPattern()}/{$expr->getOptions()}";
    }

    /**
     * @param array<string, mixed> $final
     * @param In $expr
     */
    private static function processInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key] = $expr->getValues();
    }

    /**
     * @param array<string, mixed> $final
     * @param NotIn $expr
     */
    private static function processNotInExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final['notIn'] = [$key => (array) $expr->getValues()];
    }

    /**
     * @param array<string, mixed> $final
     * @param InclusiveOr $expr
     */
    private static function processInclusiveOrExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $orValues = $expr->getValues();
        $final['or'] = self::convert($orValues);
    }

    /**
     * @param array<string, mixed> $final
     * @param ObjectReference $expr
     */
    private static function processObjectReferenceExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final[$key . '.id'] = (string) $expr->getObject()->getId();
    }

    /**
     * @param array<int|string, mixed> $final
     */
    private static function callConvert(array &$final, int|string $key, ExprInterface $value): void
    {
        $exprClass = $value::class;
        if (!isset(self::$exprMappings[$exprClass])) {
            throw new NonManagedClassException("$exprClass is not managed by this repository");
        }

        $callable = self::$exprMappings[$exprClass];
        $callable($final, (string) $key, $value);
    }

    /**
     * @param array<string|int, mixed> $criteria
     * @return array<string, mixed>
     */
    private static function sanitize(array $criteria): array
    {
        $sanitazedCriteria = [];
        foreach (self::convert($criteria) as $key => &$value) {
            $sanitazedCriteria[(string) $key] = $value;
        }

        return $sanitazedCriteria;
    }

    /**
     * @param array<string|int, mixed> $values
     * @return array<string|int, mixed>
     */
    private static function convert(array &$values): array
    {
        $final = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = self::convert($value);
            }

            if (!$value instanceof ExprInterface) {
                $final[(string) $key] = $value;

                continue;
            }

            self::callConvert(
                final: $final,
                key: $key,
                value: $value,
            );
        }

        return $final;
    }
}

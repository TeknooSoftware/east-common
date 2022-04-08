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

namespace Teknoo\East\Common\Doctrine\DBSource\Common;

use RuntimeException;
use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Query\Expr\In;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\Expr\ObjectReference;

/**
 * Default implementation dedicated to generic Doctrine component to convert East Common
 * Queries expressions to Doctrines generic queries.
 * Usable with ORM or ODM, but a optimized version dedicated to ODM is available into `ODM`
 * namespace.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait ExprConversionTrait
{
    /**
     * @var array<string, callable>
     */
    private static $exprMappings = [
        NotEqual::class => [self::class, 'processNotEqualExpr'],
        In::class => [self::class, 'processInExpr'],
        InclusiveOr::class => [self::class, 'processInclusiveOrExpr'],
        ObjectReference::class => [self::class, 'processObjectReferenceExpr'],
    ];

    public static function addExprMappingConversion(string $exprClass, callable $conversionFunction): void
    {
        self::$exprMappings[$exprClass] = $conversionFunction;
    }

    /**
     * @param array<string, mixed> $final
     * @param NotEqual $expr
     */
    private static function processNotEqualExpr(array &$final, string $key, ExprInterface $expr): void
    {
        $final['notEqual'] = [$key => $expr->getValue()];
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
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private static function convert(array &$values): array
    {
        $final = [];
        foreach ($values as $key => $value) {
            if (!$value instanceof ExprInterface) {
                $final[$key] = $value;

                continue;
            }

            $exprClass = $value::class;
            if (!isset(self::$exprMappings[$exprClass])) {
                throw new RuntimeException("$exprClass is not managed by this repository");
            }

            $callable = self::$exprMappings[$exprClass];
            $callable($final, (string) $key, $value);
        }

        return $final;
    }
}

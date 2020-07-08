<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\DBSource\ODM;

use Teknoo\East\Website\Query\Expr\ExprInterface;
use Teknoo\East\Website\Query\Expr\In;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait ExprConversionTrait
{
    private function convert(array &$values): array
    {
        $final = [];
        foreach ($values as $key => $value) {
            if (!$value instanceof ExprInterface) {
                $final[$key] = $value;

                continue;
            }

            if ($value instanceof In) {
                $final[$key] = ['$in' => $value];
            }
        }

        return $final;
    }
}
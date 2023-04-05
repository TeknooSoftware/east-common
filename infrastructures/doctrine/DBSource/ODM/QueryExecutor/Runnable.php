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
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;

use Closure;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;
use Throwable;

/**
 * State of QueryExecutor when the method "filterOn" is called and the document class was set.
 * This state implement the run behavior, to create the query via the Dotrine ODM Query builder and execute it.
 * The result is passed to the promise
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin QueryExecutor
 */
class Runnable implements StateInterface
{
    use StateTrait;

    private function run(): Closure
    {
        return function (PromiseInterface $promise): void {
            $builder = $this->documentManager->createQueryBuilder($this->className);
            $builder = ($this->queryTypeInitializer)($builder);
            $builder->equals(self::convert($this->criteria));

            if (!empty($this->fields)) {
                foreach ($this->fields as $fieldName => &$value) {
                    $builder->field($fieldName)->set($value);
                }
            }

            try {
                $query = $builder->getQuery();
                $result = $query->execute();

                $promise->success($result);
            } catch (Throwable $error) {
                $promise->fail($error);
            }
        };
    }
}

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

namespace Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;

use Closure;
use RuntimeException;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * Default state of QueryExecutor until the document class is not set via the method `filterOn`
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin QueryExecutor
 */
class Pending implements StateInterface
{
    use StateTrait;

    /**
     * @param PromiseInterface<mixed, mixed> $promise
     */
    private function run(): Closure
    {
        return function (PromiseInterface $promise): void {
            $promise->fail(new RuntimeException('The query is not configured', 400));
        };
    }
}

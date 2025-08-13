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

namespace Teknoo\East\Common\Object;

use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\Exception\BadMethodCallException;

use function is_array;
use function is_callable;
use function is_string;

/**
 * Trait to implement all VisitableInterface
 *
 * @see VisitableInterface
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait VisitableTrait
{
    public function visit(string|array $visitors, ?callable $callable = null): VisitableInterface
    {
        if (is_array($visitors) && is_callable($callable)) {
            throw new BadMethodCallException(
                '$callable is forbidden when $visitors is an array with the Visitable contract'
            );
        }

        if (is_string($visitors) && !is_callable($callable)) {
            throw new BadMethodCallException(
                '$callable is mandatory when $visitors is a string with the Visitable contract'
            );
        }

        if (is_string($visitors)) {
            $visitors = [$visitors => $callable];
        }

        $this->runVisit($visitors);

        return $this;
    }

    /**
     * @param array<string, callable> $visitors
     */
    private function runVisit(array &$visitors): void
    {
        foreach ($visitors as $property => $toCall) {
            if (isset($this->{$property})) {
                $toCall($this->{$property});
            }
        }
    }
}

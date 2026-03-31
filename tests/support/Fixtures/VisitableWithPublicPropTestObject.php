<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

use Teknoo\East\Common\Contracts\Object\VisitableInterface;

use function is_string;

class VisitableWithPublicPropTestObject implements VisitableInterface
{
    public string $name = '';

    public function visit(string|array $visitors, ?callable $callable = null): VisitableInterface
    {
        if (is_string($visitors)) {
            $visitors = [$visitors => $callable];
        }

        foreach ($visitors as $property => $callback) {
            if (isset($this->{$property})) {
                $callback($this->{$property});
            }
        }

        return $this;
    }
}

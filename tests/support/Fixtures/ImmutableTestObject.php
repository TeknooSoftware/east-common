<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

use Teknoo\Immutable\ImmutableInterface;

class ImmutableTestObject implements ImmutableInterface
{
    public function __construct(
        public readonly string $value = '',
        public readonly int $count = 0,
    ) {
    }

    public function __set(string $name, mixed $value): never
    {
        throw new \RuntimeException('Immutable');
    }

    public function __unset(string $name): never
    {
        throw new \RuntimeException('Immutable');
    }
}

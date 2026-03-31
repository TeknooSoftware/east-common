<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class ImmutableWithGetterTestObject
{
    public function __construct(
        private readonly string $value = '',
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

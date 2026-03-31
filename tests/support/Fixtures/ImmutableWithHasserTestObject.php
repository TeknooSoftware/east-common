<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class ImmutableWithHasserTestObject
{
    public function __construct(
        private readonly bool $active = false,
    ) {
    }

    public function hasActive(): bool
    {
        return $this->active;
    }
}

<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class ImmutableWithIsserTestObject
{
    public function __construct(
        private readonly bool $enabled = false,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}

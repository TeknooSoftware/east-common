<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class ImmutableNullableNoDefaultTestObject
{
    public function __construct(
        public readonly ?string $value,
        public readonly ?int $count,
    ) {
    }
}

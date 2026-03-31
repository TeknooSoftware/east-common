<?php

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Support\Fixtures;

class MutableWithReadonlyObject
{
    public function __construct(
        public readonly int $id,
    ) {
    }
}

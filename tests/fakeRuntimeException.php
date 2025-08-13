<?php

declare(strict_types=1);

namespace MongoDB\Exception;

if (!\class_exists(\MongoDB\Exception\RuntimeException::class, false)) {
    class RuntimeException extends \RuntimeException
    {
    }
}

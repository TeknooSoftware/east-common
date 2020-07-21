<?php

namespace MongoDB\Exception;

if (!\class_exists('MongoDB\Exception\RuntimeException')) {
    class RuntimeException extends \RuntimeException
    {
    }
}

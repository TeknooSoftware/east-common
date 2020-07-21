<?php

namespace MongoDB\Exception;

if (!\class_exists('MongoDB\Exception\RuntimeException', false)) {
    class RuntimeException extends \RuntimeException
    {
    }
}

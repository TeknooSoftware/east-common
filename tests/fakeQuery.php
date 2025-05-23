<?php

namespace Doctrine\ODM\MongoDB\Query;

use Doctrine\ODM\MongoDB\Iterator\IterableResult;
use Doctrine\ODM\MongoDB\Iterator\Iterator;

if (!\class_exists(Query::class, false)) {
    class Query implements IterableResult
    {
        final public const TYPE_FIND            = 1;
        final public const TYPE_FIND_AND_UPDATE = 2;
        final public const TYPE_FIND_AND_REMOVE = 3;
        final public const TYPE_INSERT          = 4;
        final public const TYPE_UPDATE          = 5;
        final public const TYPE_REMOVE          = 6;
        final public const TYPE_DISTINCT        = 9;
        final public const TYPE_COUNT           = 11;

        final public const HINT_REFRESH = 1;
        // 2 was used for HINT_SLAVE_OKAY, which was removed in 2.0
        final public const HINT_READ_PREFERENCE = 3;
        final public const HINT_READ_ONLY       = 5;

        public $resultToReturn = [];

        /**
         * @var iterable|int
         */
        public function execute(): mixed
        {
            return $this->resultToReturn;
        }

        /**
         * @return array|object|null
         */
        public function getSingleResult(): mixed
        {

        }

        public function setHydrate(bool $hydrate) : void
        {

        }

        public function getIterator(): Iterator
        {
            // TODO: Implement getIterator() method.
        }
    }
}

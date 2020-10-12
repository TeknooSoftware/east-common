<?php

namespace Doctrine\ODM\MongoDB\Query;

if (!\class_exists(Query::class, false)) {
    class Query
    {
        public const TYPE_FIND            = 1;
        public const TYPE_FIND_AND_UPDATE = 2;
        public const TYPE_FIND_AND_REMOVE = 3;
        public const TYPE_INSERT          = 4;
        public const TYPE_UPDATE          = 5;
        public const TYPE_REMOVE          = 6;
        public const TYPE_DISTINCT        = 9;
        public const TYPE_COUNT           = 11;

        public const HINT_REFRESH = 1;
        // 2 was used for HINT_SLAVE_OKAY, which was removed in 2.0
        public const HINT_READ_PREFERENCE = 3;
        public const HINT_READ_ONLY       = 5;

        /**
         * @var iterable|int
         */
        public function execute()
        {
            return [];
        }

        /**
         * @return array|object|null
         */
        public function getSingleResult()
        {

        }

        public function setHydrate(bool $hydrate) : void
        {

        }
    }
}

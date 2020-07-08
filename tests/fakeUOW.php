<?php

namespace Doctrine\ODM\MongoDB;

use Doctrine\Common\PropertyChangedListener;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

if (!\class_exists(UnitOfWork::class, false)) {
    class UnitOfWork implements PropertyChangedListener
    {
        public function propertyChanged($sender, $propertyName, $oldValue, $newValue)
        {
        }

        public function getDocumentChangeSet(object $document) : array
        {
        }

        public function clearDocumentChangeSet(string $oid)
        {
        }

        public function recomputeSingleDocumentChangeSet(ClassMetadata $class, object $document) : void
        {
        }

        public function setOriginalDocumentProperty(string $oid, string $property, $value) : void
        {
        }

        public function getScheduledDocumentInsertions() : array
        {
        }

        public function getScheduledDocumentUpdates() : array
        {
        }

        public function getScheduledDocumentDeletions() : array
        {

        }
    }
}

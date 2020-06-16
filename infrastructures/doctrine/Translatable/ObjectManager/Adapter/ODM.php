<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\ObjectManager\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

class ODM implements AdapterInterface
{
    private ManagerInterface $eastManager;

    private DocumentManager $doctrineManager;

    private ?UnitOfWork $unitOfWork = null;

    public function __construct(ManagerInterface $eastManager, DocumentManager $doctrineManager)
    {
        $this->eastManager = $eastManager;
        $this->doctrineManager = $doctrineManager;
    }

    public function persist($object): ManagerInterface
    {
        $this->eastManager->persist($object);

        return $this;
    }

    public function remove($object): ManagerInterface
    {
        $this->eastManager->remove($object);

        return $this;
    }

    public function flush(): ManagerInterface
    {
        $this->eastManager->flush();

        return $this;
    }

    public function getRootObject(): ObjectManager
    {
        return $this->doctrineManager;
    }

    public function getClassMetadata(string $class): BaseClassMetadata
    {
        return $this->doctrineManager->getClassMetadata($class);
    }

    private function getUnitOfWork(): UnitOfWork
    {
        if (null === $this->unitOfWork) {
            $this->unitOfWork = $this->doctrineManager->getUnitOfWork();
        }

        return $this->unitOfWork;
    }

    public function ifObjectHasChangeSet(TranslatableInterface $object, callable $callback): AdapterInterface
    {
        $changeSet = $this->getUnitOfWork()->getDocumentChangeSet($object);

        if (!empty($changeSet)) {
            $callback($changeSet);
        }

        return $this;
    }

    public function recomputeSingleObjectChangeSet(
        BaseClassMetadata $metadata,
        TranslatableInterface $object
    ): AdapterInterface {
        $uow = $this->getUnitOfWork();
        $uow->clearDocumentChangeSet(\spl_object_hash($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);
    }

    public function foreachScheduledObjectInsertions(callable $callback): AdapterInterface
    {
        foreach ($this->getUnitOfWork()->getScheduledDocumentInsertions() as $document) {
            $callback($document);
        }

        return $this;
    }

    public function foreachScheduledObjectUpdates(callable $callback): AdapterInterface
    {
        foreach ($this->getUnitOfWork()->getScheduledDocumentUpdates() as $document) {
            $callback($document);
        }

        return $this;
    }

    public function foreachScheduledObjectDeletions(callable $callback): AdapterInterface
    {
        foreach ($this->getUnitOfWork()->getScheduledDocumentDeletions() as $document) {
            $callback($document);
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setOriginalObjectProperty(string $oid, string $property, $value): AdapterInterface
    {
        $this->getUnitOfWork()->setOriginalDocumentProperty($oid, $property, $value);
    }
}

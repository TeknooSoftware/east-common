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
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableInterface;

class ODM implements AdapterInterface
{
    private DocumentManager $manager;

    private ?UnitOfWork $unitOfWork = null;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getRootObject(): ObjectManager
    {
        return $this->manager;
    }

    public function getClassMetadata(string $class): ClassMetadata
    {
        return $this->manager->getClassMetadata($class);
    }

    private function getUnitOfWork(): UnitOfWork
    {
        if (null === $this->unitOfWork) {
            $this->unitOfWork = $this->manager->getUnitOfWork();
        }

        return $this->unitOfWork;
    }

    public function tryGetById($id, ClassMetadata $class): ?TranslatableInterface
    {
        return $this->getUnitOfWork()->tryGetById($id, $class);
    }

    public function scheduleForUpdate(TranslatableInterface $document): void
    {
        $this->getUnitOfWork()->scheduleForUpdate($document);
    }

    public function getObjectChangeSet(TranslatableInterface $object): array
    {
        return $this->getUnitOfWork()->getDocumentChangeSet($object);
    }

    public function recomputeSingleObjectChangeSet(ClassMetadata $meta, TranslatableInterface $object): void
    {
        $this->getUnitOfWork()->recomputeSingleDocumentChangeSet($meta, $object);
    }

    public function getScheduledObjectUpdates(): array
    {
        return $this->getUnitOfWork()->getScheduledDocumentUpdates();
    }

    public function getScheduledObjectInsertions(): array
    {
        return $this->getUnitOfWork()->getScheduledDocumentInsertions();
    }

    public function getScheduledObjectDeletions(): array
    {
        return $this->getUnitOfWork()->getScheduledDocumentDeletions();
    }

    public function setOriginalObjectProperty(string $oid, string $property, $value): void
    {
        $this->getUnitOfWork()->setOriginalDocumentProperty($oid, $property, $value);
    }

    public function clearObjectChangeSet(string $oid): void
    {
        $this->getUnitOfWork()->clearDocumentChangeSet($oid);
    }
}

<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\ObjectManager\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use RuntimeException;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\Object\TranslatableInterface;

use function spl_object_hash;

/**
 * Implementation of adapter dedicated to Doctrine ODM Manager to use it into this library as Object Manager to update
 * objects's states.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ODM implements AdapterInterface
{
    private ?UnitOfWork $unitOfWork = null;

    public function __construct(
        private ManagerInterface $eastManager,
        private DocumentManager $doctrineManager,
    ) {
    }

    public function persist(object $object): ManagerInterface
    {
        $this->eastManager->persist($object);

        return $this;
    }

    public function remove(object $object): ManagerInterface
    {
        $this->eastManager->remove($object);

        return $this;
    }

    public function flush(): ManagerInterface
    {
        $this->eastManager->flush();

        return $this;
    }

    public function findClassMetadata(string $class, TranslatableListener $listener): AdapterInterface
    {
        $listener->registerClassMetadata(
            $class,
            $this->doctrineManager->getClassMetadata($class)
        );

        return $this;
    }

    private function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork ??= $this->doctrineManager->getUnitOfWork();
    }

    public function ifObjectHasChangeSet(TranslatableInterface $object, callable $callback): AdapterInterface
    {
        $changeSet = $this->getUnitOfWork()->getDocumentChangeSet($object);

        if (!empty($changeSet)) {
            $callback($changeSet);
        }

        return $this;
    }

    /**
     * @param BaseClassMetadata<ClassMetadata> $metadata
     */
    public function recomputeSingleObjectChangeSet(
        BaseClassMetadata $metadata,
        TranslatableInterface $object
    ): AdapterInterface {
        if (!$metadata instanceof ClassMetadata) {
            throw new RuntimeException('Error this classMetada is not compatible with the document manager');
        }

        $uow = $this->getUnitOfWork();
        $uow->clearDocumentChangeSet(spl_object_hash($object));
        $uow->recomputeSingleDocumentChangeSet($metadata, $object);

        return $this;
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

    public function setObjectPropertyInManager(string $oid, string $property, mixed $value): AdapterInterface
    {
        $this->getUnitOfWork()->setOriginalDocumentProperty($oid, $property, $value);

        return $this;
    }
}

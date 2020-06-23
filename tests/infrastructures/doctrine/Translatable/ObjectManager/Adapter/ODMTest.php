<?php

/**
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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\ObjectManager\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\DBSource\ManagerInterface;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\Adapter\ODM;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableListener;
use Teknoo\East\Website\Object\TranslatableInterface;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\ObjectManager\Adapter\ODM
 */
class ODMTest extends TestCase
{
    private ManagerInterface $eastManager;

    private DocumentManager $doctrineManager;

    /**
     * @return ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getEastManager(): ManagerInterface
    {
        if (!$this->eastManager instanceof ManagerInterface) {
            $this->eastManager = $this->createMock(ManagerInterface::class);
        }

        return $this->eastManager;
    }

    /**
     * @return DocumentManager|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDoctrineManager(): DocumentManager
    {
        if (!$this->doctrineManager instanceof DocumentManager) {
            $this->doctrineManager = $this->createMock(DocumentManager::class);
        }

        return $this->doctrineManager;
    }

    public function build(): ODM
    {
        return new ODM($this->getEastManager(), $this->getDoctrineManager());
    }

    public function testPersist()
    {
        $object = new \stdClass();
        $this->getEastManager()->expects(self::once())->method('persist')->with($object);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->persist($object)
        );
    }

    public function testRemove()
    {
        $object = new \stdClass();
        $this->getEastManager()->expects(self::once())->method('remove')->with($object);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->remove($object)
        );
    }

    public function testFlush()
    {
        $object = new \stdClass();
        $this->getEastManager()->expects(self::once())->method('flush')->with($object);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->flush($object)
        );
    }

    public function testFindClassMetadata()
    {
        $class = 'Foo\Bar';
        $meta = $this->createMock(ClassMetadata::class);

        $this->getDoctrineManager()
            ->expects(self::once())
            ->method('getClassMetadata')
            ->with($class)
            ->willReturn($meta);

        $listener = $this->createMock(TranslatableListener::class);
        $listener->expects(self::once())->method('registerClassMetadata')->with($class, $meta);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->findClassMetadata($class, $listener)
        );
    }

    public function testIfObjectHasChangeSetEmpty()
    {
        $object = $this->createMock(TranslatableInterface::class);

        $changset = [];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $neverCallback = function () {
            self::fail('must not be called');
        };

        self::assertInstanceOf(
            ODM::class,
            $this->build()->ifObjectHasChangeSet($object, $neverCallback)
        );
    }

    public function testIfObjectHasChangeSet()
    {
        $object = $this->createMock(TranslatableInterface::class);

        $changset = ['foo' => ['bar', 'baba']];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);
    }

    public function testRecomputeSingleObjectChangeSet()
    {
    }

    public function testForeachScheduledObjectInsertions()
    {
    }

    public function testForeachScheduledObjectUpdates()
    {
    }

    public function testForeachScheduledObjectDeletions()
    {
    }

    public function testSetOriginalObjectProperty()
    {
    }
}

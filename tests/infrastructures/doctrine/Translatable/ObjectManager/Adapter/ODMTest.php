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
    private ?ManagerInterface $eastManager = null;

    private ?DocumentManager $doctrineManager = null;

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
        $this->getEastManager()->expects(self::once())->method('flush')->with();

        self::assertInstanceOf(
            ODM::class,
            $this->build()->flush()
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

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $neverCallback = function () {
            self::fail('must not be called');
        };

        $uow->expects(self::any())->method('getDocumentChangeSet')->willReturn([]);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->ifObjectHasChangeSet($object, $neverCallback)
        );
    }

    public function testIfObjectHasChangeSet()
    {
        $object = $this->createMock(TranslatableInterface::class);

        $changset = ['foo1' => ['bar', 'baba'], 'foo2' => ['bar', 'baba']];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::any())->method('getDocumentChangeSet')->willReturn($changset);

        $called = false;

        self::assertInstanceOf(
            ODM::class,
            $this->build()->ifObjectHasChangeSet($object, function () use (&$called) {
                $called = true;
            })
        );

        self::assertTrue($called);
    }

    public function testRecomputeSingleObjectChangeSetWithGenericClassMetaData()
    {
        $this->expectException(\RuntimeException::class);

        $meta = $this->createMock(BaseClassMetadata::class);
        $object = $this->createMock(TranslatableInterface::class);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::never())->method('clearDocumentChangeSet');
        $uow->expects(self::never())->method('recomputeSingleDocumentChangeSet');

        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $this->build()->recomputeSingleObjectChangeSet($meta, $object);
    }

    public function testRecomputeSingleObjectChangeSet()
    {
        $meta = $this->createMock(ClassMetadata::class);
        $object = $this->createMock(TranslatableInterface::class);

        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('clearDocumentChangeSet');
        $uow->expects(self::once())->method('recomputeSingleDocumentChangeSet');

        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->recomputeSingleObjectChangeSet($meta, $object)
        );
    }

    public function testForeachScheduledObjectInsertions()
    {
        $list = [new \stdClass(), new \stdClass()];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::any())->method('getScheduledDocumentInsertions')->willReturn($list);

        $counter = 0;

        self::assertInstanceOf(
            ODM::class,
            $this->build()->foreachScheduledObjectInsertions(function () use (&$counter) {
                $counter++;
            })
        );

        self::assertEquals(2, $counter);
    }

    public function testForeachScheduledObjectUpdates()
    {
        $list = [new \stdClass(), new \stdClass()];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::any())->method('getScheduledDocumentUpdates')->willReturn($list);

        $counter = 0;

        self::assertInstanceOf(
            ODM::class,
            $this->build()->foreachScheduledObjectUpdates(function () use (&$counter) {
                $counter++;
            })
        );

        self::assertEquals(2, $counter);
    }

    public function testForeachScheduledObjectDeletions()
    {
        $list = [new \stdClass(), new \stdClass()];

        $uow = $this->createMock(UnitOfWork::class);
        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::any())->method('getScheduledDocumentDeletions')->willReturn($list);

        $counter = 0;

        self::assertInstanceOf(
            ODM::class,
            $this->build()->foreachScheduledObjectDeletions(function () use (&$counter) {
                $counter++;
            })
        );

        self::assertEquals(2, $counter);
    }

    public function testSetOriginalObjectProperty()
    {
        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects(self::once())->method('setOriginalDocumentProperty');

        $this->getDoctrineManager()
            ->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        self::assertInstanceOf(
            ODM::class,
            $this->build()->setOriginalObjectProperty('foo', 'bar', 'hello')
        );
    }
}

<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Query\Expr\Greater;
use Teknoo\East\Common\Query\Expr\In;
use Teknoo\East\Common\Query\Expr\Lower;
use Teknoo\East\Common\Query\Expr\NotIn;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Common\Query\Expr\Regex;
use Teknoo\East\Common\Query\Expr\StrictlyGreater;
use Teknoo\East\Common\Query\Expr\StrictlyLower;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait RepositoryTestTrait
{
    /**
     * @var DocumentRepository
     */
    private $objectRepository;

    /**
     * @return DocumentRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineObjectRepositoryMock(): DocumentRepository
    {
        if (!$this->objectRepository instanceof DocumentRepository) {
            $this->objectRepository = $this->createMock(DocumentRepository::class);
        }

        return $this->objectRepository;
    }

    /**
     * @return RepositoryInterface
     */
    abstract public function buildRepository(): RepositoryInterface;

    public function testFindBadId()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->find(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->find('abc', new \stdClass());
    }

    public function testFindNothing()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail')->with($this->callback(fn($value) => $value instanceof \DomainException));

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn(null);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with(['_id' => 'abc'])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
        );
    }

    public function testFindOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with(['_id' => 'abc'])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
        );
    }

    public function testFindOneThingWithPrime()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with(['_id' => 'abc'])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('field')
            ->with('foo')
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('prime')
            ->with(true)
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise, ['foo'])
        );
    }

    public function testFindAllBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findAll(new \stdClass());
    }

    public function testFindAll()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findAll($promise)
        );
    }

    public function testFindAllWithPrime()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('field')
            ->with('foo')
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('prime')
            ->with(true)
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findAll($promise, ['foo'])
        );
    }

    public function testFindByBadCriteria()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindByBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(['foo' => 'bar'], new \stdClass());
    }

    public function testFindBy()
    {
        $this->objectRepository = $this->createMock(DocumentRepository::class);

        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->objectRepository
            ->expects($this->never())
            ->method('findBy');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->objectRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()
                ->findBy(['foo' => 'bar', 'bar' => new In(['foo'])], $promise, ['foo'=>Direction::Asc], 1, 2)
        );
    }

    public function testFindByWithPrime()
    {
        $this->objectRepository = $this->createMock(DocumentRepository::class);

        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->objectRepository
            ->expects($this->never())
            ->method('findBy');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('field')
            ->with('foo')
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('prime')
            ->with(true)
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->objectRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findBy(
                ['foo' => 'bar', 'bar' => new In(['foo'])],
                $promise,
                ['foo'=>Direction::Asc],
                1,
                2,
                ['foo'],
            )
        );
    }

    public function testFindByBadOrder()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(
            ['foo' => 'bar'],
            $this->createMock(PromiseInterface::class),
            new \stdClass()
        );
    }

    public function testCount()
    {
        $this->objectRepository = $this->createMock(DocumentRepository::class);

        $count = 123;
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($count);
        $promise->expects($this->never())->method('fail');

        $this->objectRepository
            ->expects($this->never())
            ->method('findBy');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($count);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->objectRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->count(['foo' => 'bar', 'bar' => new In(['foo'])], $promise)
        );
    }

    public function testDistinctBy()
    {
        $this->objectRepository = $this->createMock(DocumentRepository::class);

        $values = ['foo', 'bar'];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($values);
        $promise->expects($this->never())->method('fail');

        $this->objectRepository
            ->expects($this->never())
            ->method('findBy');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('execute')->willReturn($values);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->objectRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->distinctBy('foo', ['foo' => 'bar', 'bar' => new In(['foo'])], $promise, 1, 2)
        );
    }

    public function testFindOneByBadCriteria()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findOneBy(new \stdClass(), $this->createMock(PromiseInterface::class));
    }

    public function testFindOneByBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findOneBy(['foo' => 'bar'], new \stdClass());
    }

    public function testFindOneByNothing()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())
            ->method('fail')
            ->with($this->callback(fn($value) => $value instanceof \DomainException));

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn(null);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with(['foo' => 'bar'])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByExpcetion()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail')->with($this->callback(fn($value) => $value instanceof \RuntimeException));

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willThrowException(new \RuntimeException());

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with(['foo' => 'bar'])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);
        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with([
                'foo' => 'bar',
                'bar' => ['$in' => ['foo']],
                'bar2' => ['$nin' => ['foo']],
                '$ne' => ['barNot' => ['foo']],
                'barGte' => ['$gte' => 123],
                'barLte' => ['$lte' => 456],
                'barGt' => ['$gt' => 789],
                'barLt' => ['$lt' => 654],
                'barRegex' => new \MongoDB\BSON\Regex('foo', 'i'),
                '$or' => [
                    ['foo' => 'bar'],
                    ['bar' => 'foo']
                ],
                'hello.$id' => '',
            ])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(
                [
                    'foo' => 'bar',
                    'bar' => new In(['foo']),
                    'bar2' => new NotIn(['foo']),
                    'barNot' => new NotEqual(['foo']),
                    'barGte' => new Greater(123),
                    'barLte' => new Lower(456),
                    'barGt' => new StrictlyGreater(789),
                    'barLt' => new StrictlyLower(654),
                    'barRegex' => new Regex('foo', 'i'),
                    new InclusiveOr(
                        ['foo' => 'bar'],
                        ['bar' => 'foo']
                    ),
                    'hello' => new ObjectReference($this->createMock(IdentifiedObjectInterface::class))
                ],
                $promise
            )
        );
    }

    public function testFindOneByOneThingWithPrime()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $query = $this->createMock(Query::class);
        $query->expects($this->once())->method('getSingleResult')->willReturn($object);

        $queryBuilderMock = $this->createMock(Builder::class);

        $queryBuilderMock->expects($this->any())
            ->method('field')
            ->with('foo')
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('prime')
            ->with(true)
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('equals')
            ->with([
                'foo' => 'bar',
                'bar' => ['$in' => ['foo']],
                'bar2' => ['$nin' => ['foo']],
                '$ne' => ['barNot' => ['foo']],
                'barGte' => ['$gte' => 123],
                'barLte' => ['$lte' => 456],
                'barGt' => ['$gt' => 789],
                'barLt' => ['$lt' => 654],
                '$or' => [
                    ['foo' => 'bar'],
                    ['bar' => 'foo']
                ],
                'hello.$id' => '',
            ])
            ->willReturnSelf();

        $queryBuilderMock->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(
                [
                    'foo' => 'bar',
                    'bar' => new In(['foo']),
                    'bar2' => new NotIn(['foo']),
                    'barNot' => new NotEqual(['foo']),
                    'barGte' => new Greater(123),
                    'barLte' => new Lower(456),
                    'barGt' => new StrictlyGreater(789),
                    'barLt' => new StrictlyLower(654),
                    new InclusiveOr(
                        ['foo' => 'bar'],
                        ['bar' => 'foo']
                    ),
                    'hello' => new ObjectReference($this->createMock(IdentifiedObjectInterface::class))
                ],
                $promise,
                ['foo']
            )
        );
    }
}

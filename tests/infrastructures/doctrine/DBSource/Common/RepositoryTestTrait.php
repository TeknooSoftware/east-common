<?php

/**
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

namespace Teknoo\Tests\East\Website\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectRepository;
use Teknoo\East\Website\Query\Enum\Direction;
use Teknoo\East\Website\Query\Expr\NotEqual;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Query\Expr\ExprInterface;
use Teknoo\East\Website\Query\Expr\In;
use Teknoo\East\Website\Query\Expr\InclusiveOr;
use Teknoo\East\Website\Query\Expr\ObjectReference;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait RepositoryTestTrait
{
    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * @return ObjectRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineObjectRepositoryMock(): ObjectRepository
    {
        if (!$this->objectRepository instanceof ObjectRepository) {
            $this->objectRepository = $this->createMock(ObjectRepository::class);
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
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \DomainException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('find')
            ->with('abc')
            ->willReturn(null);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
        );
    }

    public function testFindOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('find')
            ->with('abc')
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->find('abc', $promise)
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
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findAll')
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findAll($promise)
        );
    }

    public function testCountNotImplemented()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->count(['foo' => 'bar'], $promise)
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

    public function testFindByBadOrder()
    {
        $this->expectException(\TypeError::class);
        $this->buildRepository()->findBy(
            ['foo' => 'bar'],
            $this->createMock(PromiseInterface::class),
            new \stdClass()
        );
    }

    public function testFindBy()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findBy(
                ['foo' => 'bar'],
                $promise,
                ['foo' => Direction::Asc]
            )
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
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \DomainException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['foo' => 'bar'])
            ->willReturn(null);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByExpcetion()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail')->with($this->callback(function ($value) {
            return $value instanceof \RuntimeException;
        }));

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['foo' => 'bar'])
            ->willThrowException(new \RuntimeException());

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(['foo' => 'bar'], $promise)
        );
    }

    public function testFindOneByOneThing()
    {
        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'foo' => 'bar',
                'bar' => ['foo'],
                'notEqual' => ['bwrNot' =>'foo'],
                'or' => [
                    ['foo' => 'bar'],
                    ['bar' => 'foo']
                ],
                'hello.id' => '',
            ])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(
                [
                    'foo' => 'bar',
                    'bar' => new In(['foo']),
                    'bwrNot' => new NotEqual('foo'),
                    new InclusiveOr(
                        ['foo' => 'bar'],
                        ['bar' => 'foo']
                    ),
                    'hello' => new ObjectReference($this->createMock(ObjectInterface::class))
                ],
                $promise
            )
        );
    }

    public function testConvertExprWithoutManaged()
    {
        $expr = new class implements ExprInterface {

        };

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::never())
            ->method('findOneBy');

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(
                [
                    $expr
                ],
                $promise
            )
        );
    }

    public function testAddExprMappingConversion()
    {
        $expr = $this->createMock(ExprInterface::class);

        $class = \get_class($this->buildRepository());
        $class::addExprMappingConversion(
            \get_class($expr),
            static function (array &$final, string $key, ExprInterface $expr) {
                $final['foo'] = 'bar';
            }
            );

        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')->with($object);
        $promise->expects(self::never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'foo' => 'bar',
            ])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->findOneBy(
                [
                    $expr
                ],
                $promise
            )
        );
    }
}

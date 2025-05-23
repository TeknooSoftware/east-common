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

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\Common;

use PHPUnit\Framework\Error\Notice;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\AssertionFailedError;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Query\Expr\ExprInterface;
use Teknoo\East\Common\Query\Enum\Direction;
use Teknoo\East\Common\Query\Expr\Greater;
use Teknoo\East\Common\Query\Expr\In;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\Expr\Lower;
use Teknoo\East\Common\Query\Expr\NotEqual;
use Teknoo\East\Common\Query\Expr\NotIn;
use Teknoo\East\Common\Query\Expr\ObjectReference;
use Teknoo\East\Common\Query\Expr\Regex;
use Teknoo\East\Common\Query\Expr\StrictlyGreater;
use Teknoo\East\Common\Query\Expr\StrictlyLower;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function restore_error_handler;

use const E_USER_NOTICE;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
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
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('find')
            ->with('abc')
            ->willReturn($object);

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

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('find')
            ->with('abc')
            ->willReturn($object);

        $fail = false;
        $previous = null;
        if (!class_exists(Notice::class)) {
            $previous = set_error_handler(
                function () use (&$fail) {
                    $fail = true;
                },
                E_USER_NOTICE
            );
        }

        try {
            self::assertInstanceOf(
                RepositoryInterface::class,
                $this->buildRepository()->find('abc', $promise, ['foo'],)
            );
        } catch (AssertionFailedError $error) {
            if (null !== $previous) {
                restore_error_handler();
            }

            throw $error;
        } catch (Throwable) {
            $fail = true;
        }

        if (null !== $previous) {
            restore_error_handler();
        }

        self::assertTrue($fail, 'Notice must be Thrown');
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

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($object);

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

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($object);

        $fail = false;
        $previous = null;
        if (!class_exists(Notice::class)) {
            $previous = set_error_handler(
                function () use (&$fail) {
                    $fail = true;
                },
                E_USER_NOTICE
            );
        }

        try {
            self::assertInstanceOf(
                RepositoryInterface::class,
                $this->buildRepository()->findAll($promise, ['foo'],)
            );
        } catch (AssertionFailedError $error) {
            if (null !== $previous) {
                restore_error_handler();
            }

            throw $error;
        } catch (Throwable) {
            $fail = true;
        }

        if (null !== $previous) {
            restore_error_handler();
        }

        self::assertTrue($fail, 'Notice must be Thrown');
    }

    public function testCount()
    {
        $object = [new \stdClass(),new \stdClass(),new \stdClass()];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('__invoke')->with(3);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->count(['foo' => 'bar'], $promise)
        );
    }

    public function testDistinctByWithArray()
    {
        $object = [
            ['foo' => 'foo'],
            ['bar' => 'bar'],
            ['foo' => 'bar'],
        ];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('__invoke')->with(['foo', 'bar']);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->distinctBy('foo', ['foo' => 'bar'], $promise)
        );
    }

    public function testDistinctByWithObject()
    {
        $object = [
            (object) ['foo' => 'foo'],
            (object) ['bar' => 'bar'],
            (object) ['foo' => 'bar'],
        ];

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('__invoke')->with(['foo', 'bar']);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        self::assertInstanceOf(
            RepositoryInterface::class,
            $this->buildRepository()->distinctBy('foo', ['foo' => 'bar'], $promise)
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
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
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

    public function testFindByWithPrime()
    {
        $object = [new \stdClass()];
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findBy')
            ->with(['foo' => 'bar'])
            ->willReturn($object);

        $fail = false;
        $previous = null;
        if (!class_exists(Notice::class)) {
            $previous = set_error_handler(
                function () use (&$fail) {
                    $fail = true;
                },
                E_USER_NOTICE
            );
        }

        try {
            self::assertInstanceOf(
                RepositoryInterface::class,
                $this->buildRepository()->findBy(
                    ['foo' => 'bar'],
                    $promise,
                    ['foo' => Direction::Asc],
                    null,
                    null,
                    ['foo'],
                )
            );
        } catch (AssertionFailedError $error) {
            if (null !== $previous) {
                restore_error_handler();
            }

            throw $error;
        } catch (Throwable) {
            $fail = true;
        }

        if (null !== $previous) {
            restore_error_handler();
        }

        self::assertTrue($fail, 'Notice must be Thrown');
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
        $promise->expects($this->once())->method('fail')->with($this->callback(fn($value) => $value instanceof \DomainException));

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
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
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail')->with($this->callback(fn($value) => $value instanceof \RuntimeException));

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
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
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'foo' => 'bar',
                'bar' => ['foo'],
                'notIn' => ['bar2' => ['foo']],
                'notEqual' => ['barNot' =>'foo'],
                'bwrRegex' => '/foo/i',
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
                    'bar2' => new NotIn(['foo']),
                    'barNot' => new NotEqual('foo'),
                    'bwrRegex' => new Regex('foo'),
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

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'foo' => 'bar',
                'bar' => ['foo'],
                'notIn' => ['bar2' => ['foo']],
                'notEqual' => ['barNot' =>'foo'],
                'barGte' => ['gte' => 123],
                'barLte' => ['lte' => 456],
                'barGt' => ['gt' => 789,],
                'barLt' => ['lt' => 654],
                'or' => [
                    ['foo' => 'bar'],
                    ['bar' => 'foo']
                ],
                'hello.id' => '',
            ])
            ->willReturn($object);

        $fail = false;
        $previous = null;
        if (!class_exists(Notice::class)) {
            $previous = set_error_handler(
                function () use (&$fail) {
                    $fail = true;
                },
                E_USER_NOTICE
            );
        }

        try {
            self::assertInstanceOf(
                RepositoryInterface::class,
                $this->buildRepository()->findOneBy(
                    [
                        'foo' => 'bar',
                        'bar' => new In(['foo']),
                        'bar2' => new NotIn(['foo']),
                        'barNot' => new NotEqual('foo'),
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
                    ['foo'],
                )
            );
        } catch (AssertionFailedError $error) {
            if (null !== $previous) {
                restore_error_handler();
            }

            throw $error;
        } catch (Throwable) {
            $fail = true;
        }

        if (null !== $previous) {
            restore_error_handler();
        }
        self::assertTrue($fail, 'Notice must be Thrown');
    }

    public function testConvertExprWithoutManaged()
    {
        $expr = new class implements ExprInterface {

        };

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->never())
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

        $class = $this->buildRepository()::class;
        $class::addExprMappingConversion(
            $expr::class,
            static function (array &$final, string $key, ExprInterface $expr) {
                $final['foo'] = 'bar';
            }
            );

        $object = new \stdClass();
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($object);
        $promise->expects($this->never())->method('fail');

        $this->getDoctrineObjectRepositoryMock()
            ->expects($this->once())
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

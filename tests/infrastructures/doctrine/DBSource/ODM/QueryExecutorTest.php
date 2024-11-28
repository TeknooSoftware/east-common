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

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Pending;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Runnable;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Runnable::class)]
#[CoversClass(Pending::class)]
#[CoversClass(QueryExecutor::class)]
class QueryExecutorTest extends TestCase
{
    private ?DocumentManager $documentManager = null;

    private function getDocumentManager(): MockObject&DocumentManager
    {
        if (null === $this->documentManager) {
            $this->documentManager = $this->createMock(DocumentManager::class);
        }

        return $this->documentManager;
    }

    private function buildQuery(callable $callback): QueryExecutor
    {
        return new QueryExecutor(
            $this->getDocumentManager(),
            $callback
        );
    }

    public function testStatesListDeclaration()
    {
        $rf = new ReflectionMethod(QueryExecutor::class, 'statesListDeclaration');
        $rf->setAccessible(true);
        self::assertIsArray($rf->getClosure()());
    }

    public function testFilterOn()
    {
        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function () {},
            )->filterOn(
                User::class,
                [
                    'id' => 'foo',
                ],
            )
        );
    }

    public function testUpdateFields()
    {
        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function () {},
            )->updateFields(
                [
                    'id' => 'foo',
                ],
            )
        );
    }

    public function testExecuteQueryNotRunnable()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail');
        $promise->expects($this->never())
            ->method('success');

        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function () {},
            )->execute(
                $promise,
            )
        );
    }

    public function testExecuteQueryRunnable()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('fail');
        $promise->expects($this->once())
            ->method('success');

        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                fn ($query) => $query,
            )
            ->filterOn(
                User::class,
                ['foo' => 'bar'],
            )
            ->execute(
                $promise,
            )
        );
    }

    public function testExecuteQueryRunnableWithError()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail');
        $promise->expects($this->never())
            ->method('success');


        $query = $this->createMock(Query::class);
        $query->expects($this->any())
            ->method('execute')
            ->willThrowException(new RuntimeException('Error'));

        $builder = $this->createMock(Builder::class);
        $builder->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDocumentManager()
            ->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                fn ($query) => $query,
            )
            ->filterOn(
                User::class,
                ['foo' => 'bar'],
            )
            ->execute(
                $promise,
            )
        );
    }

    public function testExecuteQueryRunnableWithUpdateField()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('fail');
        $promise->expects($this->once())
            ->method('success');

        self::assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                fn ($query) => $query,
            )
            ->filterOn(
                User::class,
                ['foo' => 'bar'],
            )
            ->updateFields(
                ['foo1' => 'bar1'],
                ['foo2' => 'bar2'],
            )
            ->execute(
                $promise,
            )
        );
    }
}
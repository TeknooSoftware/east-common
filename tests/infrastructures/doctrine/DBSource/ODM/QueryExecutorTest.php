<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Pending;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Runnable;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Runnable::class)]
#[CoversClass(Pending::class)]
#[CoversClass(QueryExecutor::class)]
class QueryExecutorTest extends TestCase
{
    private (DocumentManager&MockObject)|(DocumentManager&Stub)|null$documentManager = null;

    private function getDocumentManagerMock(bool $stub = false): (DocumentManager&MockObject)|(DocumentManager&Stub)
    {
        if (!$this->documentManager instanceof DocumentManager) {
            if ($stub) {
                $this->documentManager = $this->createStub(DocumentManager::class);
            } else {
                $this->documentManager = $this->createMock(DocumentManager::class);
            }
        }

        return $this->documentManager;
    }

    private function buildQuery(callable $callback): QueryExecutor
    {
        return new QueryExecutor(
            $this->getDocumentManagerMock(true),
            $callback
        );
    }

    public function testFilterOn(): void
    {
        $this->assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function (): void {},
            )->filterOn(
                User::class,
                [
                    'id' => 'foo',
                ],
            )
        );
    }

    public function testUpdateFields(): void
    {
        $this->assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function (): void {},
            )->updateFields(
                [
                    'id' => 'foo',
                ],
            )
        );
    }

    public function testExecuteQueryNotRunnable(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail');
        $promise->expects($this->never())
            ->method('success');

        $this->assertInstanceOf(
            QueryExecutor::class,
            $this->buildQuery(
                function (): void {},
            )->execute(
                $promise,
            )
        );
    }

    public function testExecuteQueryRunnable(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('fail');
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
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

    public function testExecuteQueryRunnableWithError(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail');
        $promise->expects($this->never())
            ->method('success');


        $query = $this->createStub(Query::class);
        $query
            ->method('execute')
            ->willThrowException(new RuntimeException('Error'));

        $builder = $this->createStub(Builder::class);
        $builder
            ->method('getQuery')
            ->willReturn($query);

        $this->getDocumentManagerMock(true)
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $this->assertInstanceOf(
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

    public function testExecuteQueryRunnableWithUpdateField(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())
            ->method('fail');
        $promise->expects($this->once())
            ->method('success');

        $this->assertInstanceOf(
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

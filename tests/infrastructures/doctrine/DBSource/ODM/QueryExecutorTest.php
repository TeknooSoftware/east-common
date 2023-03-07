<?php

/*
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor
 * @covers \Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Pending
 * @covers \Teknoo\East\Common\Doctrine\DBSource\ODM\QueryExecutor\Runnable
 */
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
        $promise->expects(self::once())
            ->method('fail');
        $promise->expects(self::never())
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
        $promise->expects(self::never())
            ->method('fail');
        $promise->expects(self::once())
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
        $promise->expects(self::once())
            ->method('fail');
        $promise->expects(self::never())
            ->method('success');


        $query = $this->createMock(Query::class);
        $query->expects(self::any())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Error'));

        $builder = $this->createMock(Builder::class);
        $builder->expects(self::any())
            ->method('getQuery')
            ->willReturn($query);

        $this->getDocumentManager()
            ->expects(self::any())
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
        $promise->expects(self::never())
            ->method('fail');
        $promise->expects(self::once())
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
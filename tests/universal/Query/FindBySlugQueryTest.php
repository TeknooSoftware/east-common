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

namespace Teknoo\Tests\East\Website\Query;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\FindBySlugQuery;
use Teknoo\East\Website\Query\QueryInterface;
use Teknoo\Tests\East\Website\Query\QueryTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\FindBySlugQuery
 */
class FindBySlugQueryTest extends TestCase
{
    use QueryTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(bool $includedDeleted = false): QueryInterface
    {
        return new FindBySlugQuery('fooBarName', 'HelloWorld', $includedDeleted);
    }

    public function testExecute()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld', 'deletedAt' => null,], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteWithDeleted()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['fooBarName' => 'HelloWorld',], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery(true)->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteFailing()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository) {
                $promise->fail(new \RuntimeException());

                return $repository;
            });

        self::assertInstanceOf(
            FindBySlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }
}

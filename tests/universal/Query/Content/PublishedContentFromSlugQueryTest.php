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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Query\Content;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Query\Content\PublishedContentFromSlugQuery;
use Teknoo\East\Website\Query\QueryInterface;
use Teknoo\Tests\East\Website\Query\QueryTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\Content\PublishedContentFromSlugQuery
 */
class PublishedContentFromSlugQueryTest extends TestCase
{
    use QueryTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(): QueryInterface
    {
        return new PublishedContentFromSlugQuery('fooBar');
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
            ->with(['slug' => 'fooBar', 'deletedAt' => null,], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            PublishedContentFromSlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
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
            PublishedContentFromSlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteNotPublishable()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository) {
                $promise->success(new \stdClass());

                return $repository;
            });

        self::assertInstanceOf(
            PublishedContentFromSlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecuteNotPublished()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::once())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository) {
                $object = $this->createMock(Content::class);
                $object->expects(self::any())->method('getPublishedAt')->willReturn(
                    null
                );
                $promise->success($object);

                return $repository;
            });

        self::assertInstanceOf(
            PublishedContentFromSlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }

    public function testExecutePublished()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::once())
            ->method('success')
            ->with($this->callback(function ($value) {
                return $value instanceof Content;
            }));
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($repository) {
                $content = $this->createMock(Content::class);
                $content->expects(self::any())->method('getPublishedAt')->willReturn(new \DateTime('2018-07-01'));
                $promise->success($content);

                return $repository;
            });

        self::assertInstanceOf(
            PublishedContentFromSlugQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }
}

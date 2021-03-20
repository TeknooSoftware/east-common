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

namespace Teknoo\Tests\East\Website\Query\Content;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\Expr\In;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Query\Content\PublishedContentFromIdsQuery;
use Teknoo\East\Website\Query\QueryInterface;
use Teknoo\Tests\East\Website\Query\QueryTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Query\Content\PublishedContentFromIdsQuery
 */
class PublishedContentFromIdsQueryTest extends TestCase
{
    use QueryTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(): QueryInterface
    {
        return new PublishedContentFromIdsQuery(['fooBar']);
    }

    public function testExecute()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findBy')
            ->with(['id' => new In(['fooBar']), 'deletedAt' => null,], $this->callback(function ($pr) {
                return $pr instanceof PromiseInterface;
            }));

        self::assertInstanceOf(
            PublishedContentFromIdsQuery::class,
            $this->buildQuery()->execute($loader, $repository, $promise)
        );
    }
}

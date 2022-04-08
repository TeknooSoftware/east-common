<?php

/**
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

namespace Teknoo\Tests\East\Common\Query\User;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Tests\East\Common\Query\QueryElementTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Query\User\UserByEmailQuery
 */
class UserByEmailQueryTest extends TestCase
{
    use QueryElementTestTrait;

    /**
     * @inheritDoc
     */
    public function buildQuery(): QueryElementInterface
    {
        return new UserByEmailQuery('foo@bar');
    }

    public function testFetch()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $repository = $this->createMock(RepositoryInterface::class);
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::never())->method('success');
        $promise->expects(self::never())->method('fail');

        $repository->expects(self::once())
            ->method('findOneBy')
            ->with([
                'email' => 'foo@bar',
                'active' => new InclusiveOr(
                    ['active' => true],
                    ['active' => null],
                ),
                'deletedAt' => null,],
                $promise
            );

        self::assertInstanceOf(
            UserByEmailQuery::class,
            $this->buildQuery()->fetch($loader, $repository, $promise)
        );
    }
}

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

namespace Teknoo\Tests\East\Common\Query;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait QueryElementTestTrait
{
    abstract public function buildQuery(): QueryElementInterface;

    public function testFetchBadLoader()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            new \stdClass(),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(PromiseInterface::class)
        );
    }

    public function testFetchBadRepository()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            $this->createMock(PromiseInterface::class)
        );
    }

    public function testFetchBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            $this->createMock(LoaderInterface::class),
            $this->createMock(RepositoryInterface::class),
            new \stdClass()
        );
    }
}

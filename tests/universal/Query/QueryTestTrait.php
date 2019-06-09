<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Query;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\QueryInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait QueryTestTrait
{
    /**
     * @return QueryInterface
     */
    abstract public function buildQuery(): QueryInterface;

    public function testExecuteBadLoader()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->execute(
            new \stdClass(),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(PromiseInterface::class)
        );
    }

    public function testExecuteBadRepository()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->execute(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            $this->createMock(PromiseInterface::class)
        );
    }

    public function testExecuteBadPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->execute(
            $this->createMock(LoaderInterface::class),
            $this->createMock(RepositoryInterface::class),
            new \stdClass()
        );
    }
}
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

namespace Teknoo\Tests\East\Common\Query;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait QueryElementTestTrait
{
    abstract public function buildQuery(): QueryElementInterface;

    public function testFetchBadLoader(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            new \stdClass(),
            $this->createStub(RepositoryInterface::class),
            $this->createStub(PromiseInterface::class)
        );
    }

    public function testFetchBadRepository(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            $this->createStub(LoaderInterface::class),
            new \stdClass(),
            $this->createStub(PromiseInterface::class)
        );
    }

    public function testFetchBadPromise(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildQuery()->fetch(
            $this->createStub(LoaderInterface::class),
            $this->createStub(RepositoryInterface::class),
            new \stdClass()
        );
    }
}

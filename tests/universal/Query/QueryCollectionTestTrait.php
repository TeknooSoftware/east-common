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

namespace Teknoo\Tests\East\Common\Query;

use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait QueryCollectionTestTrait
{
    abstract public function buildQuery(): QueryCollectionInterface;

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

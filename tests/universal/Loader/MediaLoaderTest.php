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

namespace Teknoo\Tests\East\Common\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\Recipe\Promise\Promise;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MediaLoader::class)] class MediaLoaderTest extends TestCase
{
    use LoaderTestTrait;

    private (RepositoryInterface&MockObject)|null $repository = null;

    public function getRepositoryMock(): RepositoryInterface&MockObject
    {
        if (!$this->repository instanceof RepositoryInterface) {
            $this->repository = $this->createMock(MediaRepositoryInterface::class);
        }

        return $this->repository;
    }

    public function buildLoader(): LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new MediaLoader($repository);
    }

    /**
     * @return Media
     */
    public function getEntity()
    {
    }

    public function testLoad(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects($this->never())->method('success');
        $promiseMock->expects($this->never())->method('fail');

        $this->getRepositoryMock()
            ->method('findOneBy')
            ->with(
                [new InclusiveOr(
                    ['id' => 'fooBar'],
                    ['metadata.legacyId' => 'fooBar',]
                )],
                $promiseMock
            );

        $this->assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->load('fooBar', $promiseMock)
        );
    }
}

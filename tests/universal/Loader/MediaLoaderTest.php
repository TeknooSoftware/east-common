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

namespace Teknoo\Tests\East\Common\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Query\Expr\InclusiveOr;
use Teknoo\Recipe\Promise\Promise;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MediaLoader::class)] class MediaLoaderTest extends TestCase
{
    use LoaderTestTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|RepositoryInterface
     */
    public function getRepositoryMock(): RepositoryInterface
    {
        if (!$this->repository instanceof RepositoryInterface) {
            $this->repository = $this->createMock(MediaRepositoryInterface::class);
        }

        return $this->repository;
    }

    /**
     * @return LoaderInterface|MediaLoader
     */
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

    public function testLoad()
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject $promiseMock
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects($this->never())->method('success');
        $promiseMock->expects($this->never())->method('fail');

        $this->getRepositoryMock()
            ->expects($this->any())
            ->method('findOneBy')
            ->with(
                [new InclusiveOr(
                    ['id'=>'fooBar'],
                    ['metadata.legacyId' => 'fooBar',]
                )],
                $promiseMock
            );

        self::assertInstanceOf(
            LoaderInterface::class,
            $this->buildLoader()->load('fooBar', $promiseMock)
        );
    }
}

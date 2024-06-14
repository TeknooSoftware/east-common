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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\Recipe\Step\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\Media as BaseMedia;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(GetStreamFromMedia::class)]
class GetStreamFromMediaTest extends TestCase
{
    private ?GridFSRepository $repository = null;

    private ?StreamFactoryInterface $streamFactory = null;

    /**
     * @return GridFSRepository|MockObject
     */
    private function getGridFSRepository(): GridFSRepository
    {
        if (!$this->repository instanceof GridFSRepository) {
            $this->repository = $this->createMock(GridFSRepository::class);
        }

        return $this->repository;
    }

    /**
     * @return StreamFactoryInterface|MockObject
     */
    private function getStreamFactory(): StreamFactoryInterface
    {
        if (!$this->streamFactory instanceof StreamFactoryInterface) {
            $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        }

        return $this->streamFactory;
    }

    public function buildStep(): GetStreamFromMedia
    {
        return new GetStreamFromMedia(
            $this->getGridFSRepository(),
            $this->getStreamFactory()
        );
    }

    public function testInvokeNotMedia()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('updateWorkPlan');
        $manager->expects($this->once())->method('error');

        self::assertInstanceOf(
            GetStreamFromMedia::class,
            $this->buildStep()(
                $this->createMock(BaseMedia::class),
                $manager
            )
        );
    }

    public function testInvokeMedia()
    {
        $stream = $this->createMock(StreamInterface::class);
        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStreamFromResource')
            ->willReturn($stream);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            StreamInterface::class => $stream,
        ]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            GetStreamFromMedia::class,
            $this->buildStep()(
                $this->createMock(Media::class),
                $manager
            )
        );
    }
}

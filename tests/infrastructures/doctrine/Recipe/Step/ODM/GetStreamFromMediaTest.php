<?php

/*
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

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Doctrine\Recipe\Step\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\Media as BaseMedia;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Common\Doctrine\Recipe\Step\ODM\GetStreamFromMedia
 */
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
        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error');

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
            ->expects(self::any())
            ->method('createStreamFromResource')
            ->willReturn($stream);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')->with([
            StreamInterface::class => $stream,
        ]);
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            GetStreamFromMedia::class,
            $this->buildStep()(
                $this->createMock(Media::class),
                $manager
            )
        );
    }
}

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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Recipe\Step\LoadMedia;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\LoadMedia
 */
class LoadMediaTest extends TestCase
{
    private ?MediaLoader $mediaLoader = null;

    /**
     * @return MediaLoader|MockObject
     */
    private function getMediaLoader(): MediaLoader
    {
        if (!$this->mediaLoader instanceof MediaLoader) {
            $this->mediaLoader = $this->createMock(MediaLoader::class);
        }

        return $this->mediaLoader;
    }

    public function buildStep(): LoadMedia
    {
        return new LoadMedia($this->getMediaLoader());
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(MediaLoader::class),
            new \stdClass()
        );
    }

    public function testInvokeFound()
    {
        $media = $this->createMock(Media::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::never())->method('error');
        $manager->expects(self::once())->method('updateWorkPlan')->with([
            Media::class => $media
        ]);

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($media) {
                    $promise->success($media);

                    return $this->getMediaLoader();
                }
            );

        self::assertInstanceOf(
            LoadMedia::class,
            $this->buildStep()(
                'foo',
                $manager
            )
        );
    }

    public function testInvokeError()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('error')->with(
            new \DomainException('foo', 404, new \DomainException('foo'))
        );
        $manager->expects(self::never())->method('updateWorkPlan');

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new \DomainException('foo'));

                    return $this->getMediaLoader();
                }
            );

        self::assertInstanceOf(
            LoadMedia::class,
            $this->buildStep()(
                'foo',
                $manager
            )
        );
    }
}

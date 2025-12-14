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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Recipe\Step\LoadMedia;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LoadMedia::class)]
class LoadMediaTest extends TestCase
{
    private (MediaLoader&Stub)|(MediaLoader&MockObject)|null $mediaLoader = null;

    private function getMediaLoader(bool $stub = false): (MediaLoader&Stub)|(MediaLoader&MockObject)
    {
        if (!$this->mediaLoader instanceof MediaLoader) {
            if ($stub) {
                $this->mediaLoader = $this->createStub(MediaLoader::class);
            } else {
                $this->mediaLoader = $this->createMock(MediaLoader::class);
            }
        }

        return $this->mediaLoader;
    }

    public function buildStep(): LoadMedia
    {
        return new LoadMedia($this->getMediaLoader(true));
    }

    public function testInvokeBadLoader(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadManager(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(MediaLoader::class),
            new \stdClass()
        );
    }

    public function testInvokeFound(): void
    {
        $media = $this->createStub(Media::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            Media::class => $media
        ]);

        $this->getMediaLoader(true)
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($media): \Teknoo\East\Common\Loader\MediaLoader {
                    $promise->success($media);

                    return $this->getMediaLoader();
                }
            );

        $this->assertInstanceOf(
            LoadMedia::class,
            $this->buildStep()(
                'foo',
                $manager
            )
        );
    }

    public function testInvokeError(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('error')->with(
            new \DomainException('foo', 404, new \DomainException('foo'))
        );
        $manager->expects($this->never())->method('updateWorkPlan');

        $this->getMediaLoader(true)
            ->method('load')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise): \Teknoo\East\Common\Loader\MediaLoader {
                    $promise->fail(new \DomainException('foo'));

                    return $this->getMediaLoader();
                }
            );

        $this->assertInstanceOf(
            LoadMedia::class,
            $this->buildStep()(
                'foo',
                $manager
            )
        );
    }
}

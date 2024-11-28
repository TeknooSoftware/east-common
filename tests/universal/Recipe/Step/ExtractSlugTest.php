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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Recipe\Step\ExtractSlug;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ExtractSlug::class)]
class ExtractSlugTest extends TestCase
{
    public function buildStep(): ExtractSlug
    {
        return new ExtractSlug();
    }

    public function testInvokeBadRequest()
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
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeWithoutPath()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['slug' => 'default']);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractSlug::class,
            $this->buildStep()(
                $request,
                $manager
            )
        );
    }

    public function testInvokeWithSimplePath()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['slug' => 'foo']);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractSlug::class,
            $this->buildStep()(
                $request,
                $manager
            )
        );
    }

    public function testInvokeWithSubPath()
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getPath')->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with(['slug' => 'bar']);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractSlug::class,
            $this->buildStep()(
                $request,
                $manager
            )
        );
    }
}

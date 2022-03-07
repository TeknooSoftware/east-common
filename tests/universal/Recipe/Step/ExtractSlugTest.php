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

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Recipe\Step\ExtractSlug;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\ExtractSlug
 */
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
        $uri->expects(self::any())->method('getPath')->willReturn('/');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')->with(['slug' => 'default']);
        $manager->expects(self::never())->method('error');

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
        $uri->expects(self::any())->method('getPath')->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')->with(['slug' => 'foo']);
        $manager->expects(self::never())->method('error');

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
        $uri->expects(self::any())->method('getPath')->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getUri')->willReturn($uri);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('updateWorkPlan')->with(['slug' => 'bar']);
        $manager->expects(self::never())->method('error');

        self::assertInstanceOf(
            ExtractSlug::class,
            $this->buildStep()(
                $request,
                $manager
            )
        );
    }
}

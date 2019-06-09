<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Service\MenuGenerator;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Middleware\MenuMiddleware
 */
class MenuMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MenuGenerator
     */
    private $menuGenerator;

    /**
     * @return MenuGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMenuGenerator()
    {
        if (!$this->menuGenerator instanceof MenuGenerator) {
            $this->menuGenerator = $this->createMock(MenuGenerator::class);
        }

        return $this->menuGenerator;
    }

    public function buildMiddleware(): MenuMiddleware
    {
        return new MenuMiddleware($this->getMenuGenerator());
    }

    public function testExecuteBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildMiddleware()->execute(
            new \stdClass(),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteBadRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildMiddleware()->execute(
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testExecuteBadManager()
    {
        $this->expectException(\TypeError::class);
        $this->buildMiddleware()->execute(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestInterface::class),
            new \stdClass()
        );
    }

    public function testExecute()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $client = $this->createMock(ClientInterface::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('continueExecution')
            ->with($client, $serverRequestFinal)
            ->willReturnSelf();

        $serverRequest->expects(self::any())
            ->method('withAttribute')
            ->with(ViewParameterInterface::REQUEST_PARAMETER_KEY, [
                'foo'=>'bar',
                'menuGenerator'=>$this->getMenuGenerator()
            ])
            ->willReturn($serverRequestFinal);

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->willReturnMap([
                [ViewParameterInterface::REQUEST_PARAMETER_KEY, [], ['foo'=>'bar']]
            ]);

        self::assertInstanceOf(
            MenuMiddleware::class,
            $this->buildMiddleware()->execute($client, $serverRequest, $manager)
        );
    }
}

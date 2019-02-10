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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouterInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\DeletableInterface;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminDeleteEndPoint;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminDeleteEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 */
class AdminDeleteEndPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeletingService
     */
    private $deletingService;

    /**
     * @var LoaderInterface
     */
    private $loaderService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @return DeletingService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDeletingService(): DeletingService
    {
        if (!$this->deletingService instanceof DeletingService) {
            $this->deletingService = $this->createMock(DeletingService::class);
        }

        return $this->deletingService;
    }

    /**
     * @return LoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoaderService(): LoaderInterface
    {
        if (!$this->loaderService instanceof LoaderInterface) {
            $this->loaderService = $this->createMock(LoaderInterface::class);
        }

        return $this->loaderService;
    }

    /**
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRouter(): RouterInterface
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    /**
     * @return \Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait|AdminDeleteEndPoint
     */
    public function buildEndPoint()
    {
        return (new AdminDeleteEndPoint())
            ->setDeletingService($this->getDeletingService())
            ->setLoader($this->getLoaderService())
            ->setRouter($this->getRouter());
    }

    public function testExceptionOnSetDeletingServiceWithBadInstance()
    {
        $this->expectException(\TypeError::class);
        (new AdminDeleteEndPoint)->setDeletingService(new \stdClass());
    }

    public function testExceptionOnInvokeWithBadRequest()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadClient()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            'foo',
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadId()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            'foo'
        );
    }

    public function testExceptionOnInvokeWithBadRoute()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new \stdClass()
        );
    }

    public function testInvokeNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $this->getDeletingService()
            ->expects(self::never())
            ->method('delete');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('load')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $promise->fail(new \DomainException());

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminDeleteEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo', 'bar')
        );
    }

    public function testInvokeFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $object = $this->createMock(DeletableInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getDeletingService()
            ->expects(self::once())
            ->method('delete')
            ->with($object)
            ->willReturnSelf();

        $this->getRouter()
            ->expects(self::once())
            ->method('generate')
            ->with('bar')
            ->willReturn('foo');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('load')
            ->willReturnCallback(function ($id, PromiseInterface $promise) use ($object) {
                $promise->success($object);

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminDeleteEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo', 'bar')
        );
    }
}

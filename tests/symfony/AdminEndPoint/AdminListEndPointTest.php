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

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Query\PaginationQuery;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminListEndPoint;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminListEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 */
class AdminListEndPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoaderInterface
     */
    private $loaderService;

    /**
     * @var EngineInterface
     */
    private $twig;

    /**
     * @return LoaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLoaderService(): LoaderInterface
    {
        if (!$this->loaderService instanceof LoaderInterface) {
            $this->loaderService = $this->createMock(LoaderInterface::class);
        }

        return $this->loaderService;
    }

    /**
     * @return EngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTwig(): EngineInterface
    {
        if (!$this->twig instanceof EngineInterface) {
            $this->twig = $this->createMock(EngineInterface::class);
        }

        return $this->twig;
    }

    public function buildEndPoint()
    {
        return (new AdminListEndPoint())
            ->setLoader($this->getLoaderService())
            ->setTemplating($this->getTwig())
            ->setViewPath('foo:bar.html.twig');
    }

    public function testExceptionOnInvokeWithBadRequest()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            123,
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadClient()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            123,
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
            123,
            new \stdClass()
        );
    }

    public function testInvokeNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('query')
            ->willReturnCallback(function ($search, PromiseInterface $promise) {
                $promise->fail(new \DomainException());

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 1, 'bar')
        );
    }

    public function testInvokeFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], [], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrdering()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn(['order' => 'foo']);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], ['foo' => 'ASC'], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrderingWithDirectionAsc()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn(['order' => 'foo', 'direction' => 'ASC']);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], ['foo' => 'ASC'], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrderingWithDirectionDesc()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn(['order' => 'foo', 'direction' => 'DESC']);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], ['foo' => 'DESC'], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrderingWithWrongDirection()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn(['order' => 'foo', 'direction' => 'foo']);

        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::never())
            ->method('query');

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrderingWithDefaultAsc()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn([]);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], ['foo' => 'ASC'], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint()->setOrder('foo', 'ASC'))($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundOrderingWithDefaultDesc()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $request->expects(self::any())
            ->method('getQueryParams')
            ->willReturn([]);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], ['foo' => 'DESC'], 15, 15), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint()->setOrder('foo', 'DESC'))($request, $client, 2, 'bar')
        );
    }

    public function testInvokeFoundPage3()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], [], 15, 30), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 3, 'bar')
        );
    }

    public function testInvokeFoundPageNegative()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], [], 15, 0), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, -1, 'bar')
        );
    }

    public function testInvokeFoundDefaultView()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::any())
            ->method('query')
            ->willReturnCallback(function ($query, PromiseInterface $promise) {
                self::assertEquals(new PaginationQuery([], [], 15, 0), $query);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client)
        );
    }

    public function testSetOrderWithBadColumn()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()->setOrder(new \stdClass(), 'foo');
    }

    public function testSetOrderWithBadDirectionType()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()->setOrder('foo', new \stdClass());
    }

    public function testSetOrderWithBadDirectionValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->buildEndPoint()->setOrder('foo', 'bar');
    }
}

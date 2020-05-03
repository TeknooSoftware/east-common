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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
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
class AdminListEndPointTest extends TestCase
{
    /**
     * @var LoaderInterface
     */
    private $loaderService;

    /**
     * @var EngineInterface
     */
    private $engine;

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
    public function getEngine(): EngineInterface
    {
        if (!$this->engine instanceof EngineInterface) {
            $this->engine = $this->createMock(EngineInterface::class);
        }

        return $this->engine;
    }

    public function buildEndPoint()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        return (new AdminListEndPoint())
            ->setLoader($this->getLoaderService())
            ->setTemplating($this->getEngine())
            ->setResponseFactory($responseFactory)
            ->setStreamFactory($streamFactory)
            ->setViewPath('foo:bar.html.engine');
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
                $iterator = new class implements Iterator, \Countable {
                    public function next()
                    {
                    }

                    public function valid()
                    {
                        return true;
                    }

                    public function rewind()
                    {
                    }

                    public function count()
                    {
                        return 1;
                    }

                    public function toArray(): array
                    {
                        return ['foo' => 'bar'];
                    }

                    public function current()
                    {
                        return 'bar';
                    }

                    public function key()
                    {
                        return 'foo';
                    }
                };
                $promise->success($iterator);

                return $this->getLoaderService();
            });

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturn('foo');

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

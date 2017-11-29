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

use Doctrine\MongoDB\Iterator;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
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
     * @var \Twig_Environment
     */
    private $twig;

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
     * @return TwigEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getTwig(): TwigEngine
    {
        if (!$this->twig instanceof TwigEngine) {
            $this->twig = $this->createMock(TwigEngine::class);
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadRequest()
    {
        ($this->buildEndPoint())(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            123,
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadClient()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            123,
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadId()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            'foo'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadRoute()
    {
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
            ->method('loadCollection')
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
            ->method('loadCollection')
            ->willReturnCallback(function ($collection, PromiseInterface $promise, $order, $limit, $page) {
                self::assertEquals(15, $page);
                self::assertEquals(15, $limit);
                self::assertEquals([], $order);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client, 2, 'bar')
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
            ->method('loadCollection')
            ->willReturnCallback(function ($collection, PromiseInterface $promise, $order, $limit, $page) {
                self::assertEquals(30, $page);
                self::assertEquals(15, $limit);
                self::assertEquals([], $order);
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
            ->method('loadCollection')
            ->willReturnCallback(function ($collection, PromiseInterface $promise, $order, $limit, $page) {
                self::assertEquals(0, $page);
                self::assertEquals(15, $limit);
                self::assertEquals([], $order);
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
            ->method('loadCollection')
            ->willReturnCallback(function ($collection, PromiseInterface $promise, $order, $limit, $page) {
                self::assertEquals(0, $page);
                self::assertEquals(15, $limit);
                self::assertEquals([], $order);
                $promise->success($this->createMock(Iterator::class));

                return $this->getLoaderService();
            });

        self::assertInstanceOf(
            AdminListEndPoint::class,
            ($this->buildEndPoint())($request, $client)
        );
    }
}

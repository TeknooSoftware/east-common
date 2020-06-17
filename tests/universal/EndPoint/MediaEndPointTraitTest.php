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

namespace Teknoo\Tests\East\Website\EndPoint;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\EndPoint\ResponseFactoryTrait;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Doctrine\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\MediaEndPointTrait
 */
class MediaEndPointTraitTest extends TestCase
{
    /**
     * @var MediaLoader
     */
    private $mediaLoader;

    /**
     * @return MediaLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMediaLoader(): MediaLoader
    {
        if (!$this->mediaLoader instanceof MediaLoader) {
            $this->mediaLoader = $this->createMock(MediaLoader::class);
        }

        return $this->mediaLoader;
    }

    /**
     * @return MediaEndPointTrait
     */
    public function buildEndPoint()
    {
        $mediaLoader = $this->getMediaLoader();

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects(self::any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects(self::any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $endPoint = new class($mediaLoader, new CallbackStreamFactory()) {
            use ResponseFactoryTrait;
            use MediaEndPointTrait;

            private $stream;

            public function setStream(StreamInterface $stream): self
            {
                $this->stream = $stream;

                return $this;
            }

            protected function getStream(Media $media): StreamInterface
            {
                return $this->stream;
            }
        };

        $endPoint->setStream($stream = $this->createMock(StreamInterface::class));
        $stream->expects(self::any())->method('__toString')->willReturn('fooBarContent');

        $endPoint->setResponseFactory($responseFactory);

        return $endPoint;
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(new \stdClass(), 'fooBar');
    }

    public function testInvokeBadId()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeFound()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    $stream = $value->getBody();
                    return 'fooBarContent' == (string) $stream;
                }

                return false;
            }))
            ->willReturnSelf();

        $client->expects(self::never())->method('errorInRequest');

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->with('fooBar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $media = new Media();

                $promise->success($media);

                return $this->getMediaLoader();
            });

        $endPoint = $this->buildEndPoint();

        $class = \get_class($endPoint);
        self::assertInstanceOf(
            $class,
            $endPoint($client, 'fooBar')
        );
    }

    public function testInvokeNotFound()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())
            ->method('acceptResponse');

        $client->expects(self::once())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->with('fooBar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $promise->fail(new \DomainException());

                return $this->getMediaLoader();
            });

        $endPoint = $this->buildEndPoint();
        $class = \get_class($endPoint);
        self::assertInstanceOf(
            $class,
            $endPoint($client, 'fooBar')
        );
    }
}

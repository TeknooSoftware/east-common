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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\Render;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\Traits\TemplateTrait
 * @covers \Teknoo\East\Website\Recipe\Step\Traits\ResponseTrait
 * @covers \Teknoo\East\Website\Recipe\Step\Render
 */
class RenderTest extends TestCase
{
    private ?ResponseFactoryInterface $responseFactory = null;

    private ?EngineInterface $templating = null;

    private ?StreamFactoryInterface $streamFactory = null;

    /**
     * @return ResponseFactoryInterface|MockObject
     */
    private function getResponseFactory(): ResponseFactoryInterface
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        }

        return $this->responseFactory;
    }

    /**
     * @return EngineInterface|MockObject
     */
    private function getEngine(): EngineInterface
    {
        if (!$this->templating instanceof EngineInterface) {
            $this->templating = $this->createMock(EngineInterface::class);
        }

        return $this->templating;
    }

    /**
     * @return StreamFactoryInterface|MockObject
     */
    private function getStreamFactory(): StreamFactoryInterface
    {
        if (!$this->streamFactory instanceof StreamFactoryInterface) {
            $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
        }

        return $this->streamFactory;
    }

    public function buildStep(): Render
    {
        return new Render($this->getEngine(), $this->getStreamFactory(), $this->getResponseFactory());
    }

    public function testInvokeBadRequest()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            'bar'
        );
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            'foo',
            'bar',
            $this->createMock(ObjectInterface::class)
        );
    }

    public function testInvokeBadTemplate()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            'foo',
            $this->createMock(ObjectInterface::class)
        );
    }

    public function testInvokeBadObjectViewKey()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new \stdClass(),
            $this->createMock(ObjectInterface::class)
        );
    }

    public function testInvokeNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects(self::any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            $this->buildStep()(
                $request,
                $client,
                'foo',
                'bar',
                $this->createMock(ObjectInterface::class)
            )
        );
    }

    public function testInvokeWithMessage()
    {
        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects(self::any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            $this->buildStep()(
                $message,
                $client,
                'foo',
                'bar',
                $this->createMock(ObjectInterface::class)
            )
        );
    }

    public function testInvokeWithTimestampableAndNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects(self::any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            $this->buildStep()(
                $request,
                $client,
                'foo',
                'bar',
                (new Content())->setUpdatedAt(new \DateTimeImmutable('2021-01-21'))
            )
        );
    }

    public function testInvokeError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('errorInRequest');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects(self::any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->fail(
                        new \Exception('foo')
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            $this->buildStep()(
                $request,
                $client,
                'foo',
                'bar',
                $this->createMock(ObjectInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(CallbackStreamInterface::class);
        $stream->expects(self::any())->method('bind')->willReturnCallback(
            function (callable $callback) use ($stream) {
                $callback();
                return $stream;
            }
        );

        $this->getStreamFactory()
            ->expects(self::any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            $this->buildStep()(
                $request,
                $client,
                'foo',
                'bar',
                $this->createMock(ObjectInterface::class)
            )
        );
    }
}

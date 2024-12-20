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

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use tidy;
use TypeError;

use function class_exists;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Render::class)]
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
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            'bar'
        );
    }

    public function testInvokeBadClient()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new stdClass(),
            'foo',
            'bar',
            $this->createMock(IdentifiedObjectInterface::class)
        );
    }

    public function testInvokeBadTemplate()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new stdClass(),
            'foo',
            $this->createMock(IdentifiedObjectInterface::class)
        );
    }

    public function testInvokeBadObjectViewKey()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new stdClass(),
            $this->createMock(IdentifiedObjectInterface::class)
        );
    }

    public function testInvokeNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects($this->any())
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
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testInvokeWithMessage()
    {
        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('<html><body><div><p>hello</p><div></body></html>');

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects($this->any())
                        ->method('__toString')
                        ->willReturn('<html><body><div><p>hello</p><div></body></html>');

                    $promise->success($result);

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
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testInvokeWithMessageAndCleanOutput()
    {
        if (!class_exists(tidy::class)) {
            self::markTestSkipped('Tidy ext is not available');

            return;
        }

        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

$output = <<<EOF
<!DOCTYPE html>
<html>
  <head>
    <title></title>
  </head>
  <body>
    <div>
      <p>
        hello
      </p>
      <div></div>
    </div>
  </body>
</html>
EOF;

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('write')
            ->with($output);

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects($this->any())
                        ->method('__toString')
                        ->willReturn('<html><body><div><p>hello</p><div></body></html>');

                    $promise->success($result);

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            actual: $this->buildStep()(
                $message,
                $client,
                'foo',
                'bar',
                $this->createMock(IdentifiedObjectInterface::class),
                cleanHtml: true,
            )
        );
    }

    public function testInvokeWithMessageAndCleanOutputAndApiEnable()
    {
        if (!class_exists(tidy::class)) {
            self::markTestSkipped('Tidy ext is not available');

            return;
        }

        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('<html><body><div><p>hello</p><div></body></html>');

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects($this->any())
                        ->method('__toString')
                        ->willReturn('<html><body><div><p>hello</p><div></body></html>');

                    $promise->success($result);

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
            Render::class,
            actual: $this->buildStep()(
                $message,
                $client,
                'foo',
                'bar',
                $this->createMock(IdentifiedObjectInterface::class),
                api: 'json',
                cleanHtml: true,
            )
        );
    }

    public function testInvokeWithTimestampableAndNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects($this->any())
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
                new class implements IdentifiedObjectInterface, PublishableInterface {
                    public function getId(): string
                    {
                        return 123;
                    }

                    public function updatedAt(): DateTimeInterface
                    {
                        return new DateTimeImmutable('2021-01-21');
                    }

                    public function getPublishedAt(): ?DateTimeInterface
                    {
                        return new DateTimeImmutable('2021-01-21');
                    }

                    public function setPublishedAt(DateTimeInterface $dateTime): PublishableInterface
                    {
                        return $this;
                    }
                }
            )
        );
    }

    public function testInvokeError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('errorInRequest');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $promise->fail(
                        new Exception('foo')
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
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(CallbackStreamInterface::class);
        $stream->expects($this->any())->method('bind')->willReturnCallback(
            function (callable $callback) use ($stream) {
                self::assertEquals(
                    '<html><body><div><p>hello</p><div></body></html>',
                    $callback()
                );
                return $stream;
            }
        );

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects($this->any())
                        ->method('__toString')
                        ->willReturn('<html><body><div><p>hello</p><div></body></html>');

                    $promise->success($result);

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
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallbackAnddCleanOutput()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

$output = <<<EOF
<!DOCTYPE html>
<html>
  <head>
    <title></title>
  </head>
  <body>
    <div>
      <p>
        hello
      </p>
      <div></div>
    </div>
  </body>
</html>
EOF;

        $stream = $this->createMock(CallbackStreamInterface::class);
        $stream->expects($this->any())->method('bind')->willReturnCallback(
            function (callable $callback) use ($stream, $output) {
                self::assertEquals(
                    $output,
                    $callback(),
                );
                return $stream;
            }
        );

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects($this->any())
                        ->method('__toString')
                        ->willReturn('<html><body><div><p>hello</p><div></body></html>');

                    $promise->success($result);

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
                $this->createMock(IdentifiedObjectInterface::class),
                cleanHtml: true,
            )
        );
    }

    public function testSetTidyConfig()
    {
        self::assertInstanceOf(
            Render::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

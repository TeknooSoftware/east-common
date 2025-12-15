<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Recipe\Step;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Throwable;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RenderError::class)]
class RenderErrorTest extends TestCase
{
    private (ResponseFactoryInterface&Stub)|(ResponseFactoryInterface&MockObject)|null $responseFactory = null;

    private (EngineInterface&Stub)|(EngineInterface&MockObject)|null $templating = null;

    private (StreamFactoryInterface&Stub)|(StreamFactoryInterface&MockObject)|null $streamFactory = null;

    private function getResponseFactory(bool $stub = false): (ResponseFactoryInterface&Stub)|(ResponseFactoryInterface&MockObject)
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            if ($stub) {
                $this->responseFactory = $this->createStub(ResponseFactoryInterface::class);
            } else {
                $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
            }
        }

        return $this->responseFactory;
    }

    private function getEngine(bool $stub = false): (EngineInterface&Stub)|(EngineInterface&MockObject)
    {
        if (!$this->templating instanceof EngineInterface) {
            if ($stub) {
                $this->templating = $this->createStub(EngineInterface::class);
            } else {
                $this->templating = $this->createMock(EngineInterface::class);
            }
        }

        return $this->templating;
    }

    private function getStreamFactory(bool $stub = false): (StreamFactoryInterface&Stub)|(StreamFactoryInterface&MockObject)
    {
        if (!$this->streamFactory instanceof StreamFactoryInterface) {
            if ($stub) {
                $this->streamFactory = $this->createStub(StreamFactoryInterface::class);
            } else {
                $this->streamFactory = $this->createMock(StreamFactoryInterface::class);
            }
        }

        return $this->streamFactory;
    }

    public function buildStep(): RenderError
    {
        return new RenderError(
            $this->getEngine(true),
            $this->getStreamFactory(true),
            $this->getResponseFactory(true),
        );
    }

    public function testInvokeBadRequest(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            $this->createStub(ClientInterface::class),
            'foo',
            $this->createStub(Throwable::class)
        );
    }

    public function testInvokeBadClient(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            new stdClass(),
            'foo',
            $this->createStub(Throwable::class),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadErrorTemplate(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(ClientInterface::class),
            new stdClass(),
            $this->createStub(Throwable::class),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeBadError(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(ClientInterface::class),
            'foo',
            new stdClass(),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testInvokeNonCallback(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($this->createStub(StreamInterface::class));

        $this->getEngine(true)
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): EngineInterface {
                    $promise->success(
                        $this->createStub(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()(
                $request,
                $client,
                $template,
                $error,
                $this->createStub(ManagerInterface::class)
            )
        );
    }

    public function testInvokeWithMessage(): void
    {
        $message = $this->createStub(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($this->createStub(StreamInterface::class));

        $this->getEngine(true)
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): EngineInterface {
                    $promise->success(
                        $this->createStub(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()(
                $message,
                $client,
                $template,
                $error,
                $this->createStub(ManagerInterface::class)
            )
        );
    }

    public function testInvokeError(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->exactly(2))
            ->method('errorInRequest');

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($this->createStub(StreamInterface::class));

        $this->getEngine(true)
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): EngineInterface {
                    $promise->fail(
                        new Exception('foo')
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()(
                $request,
                $client,
                $template,
                $error,
                $this->createStub(ManagerInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallback(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createStub(CallbackStreamInterface::class);
        $stream->method('bind')->willReturnCallback(
            function (callable $callback) use ($stream): Stub {
                $callback();
                return $stream;
            }
        );

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine(true)
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): EngineInterface {
                    $promise->success(
                        $this->createStub(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()(
                $request,
                $client,
                $template,
                $error,
                $this->createStub(ManagerInterface::class)
            )
        );
    }

    public static function codeProvider(): \Iterator
    {
        yield [400];
        yield [401];
        yield [402];
        yield [403];
        yield [404];
        yield [500];
        yield [550];
    }

    #[DataProvider('codeProvider')]
    public function testInvokeWithCode(int $code): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo', $code);

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createsTUB(StreamInterface::class);

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine(true)
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): EngineInterface {
                    $promise->success(
                        $this->createStub(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()(
                $request,
                $client,
                $template,
                $error,
                $this->createStub(ManagerInterface::class)
            )
        );
    }

    public function testSetTidyConfig(): void
    {
        $this->assertInstanceOf(
            RenderError::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

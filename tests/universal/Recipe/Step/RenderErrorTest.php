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

    public function buildStep(): RenderError
    {
        return new RenderError($this->getEngine(), $this->getStreamFactory(), $this->getResponseFactory());
    }

    public function testInvokeBadRequest(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            $this->createMock(Throwable::class)
        );
    }

    public function testInvokeBadClient(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new stdClass(),
            'foo',
            $this->createMock(Throwable::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadErrorTemplate(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new stdClass(),
            $this->createMock(Throwable::class),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeBadError(): void
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new stdClass(),
            $this->createMock(ManagerInterface::class)
        );
    }

    public function testInvokeNonCallback(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): \Teknoo\East\Foundation\Template\EngineInterface {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
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
                $this->createMock(ManagerInterface::class)
            )
        );
    }

    public function testInvokeWithMessage(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): \Teknoo\East\Foundation\Template\EngineInterface {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
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
                $this->createMock(ManagerInterface::class)
            )
        );
    }

    public function testInvokeError(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->exactly(2))
            ->method('errorInRequest');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->method('createResponse')
            ->willReturn($response);

        $this->getStreamFactory()
            ->method('createStream')
            ->willReturn($this->createMock(StreamInterface::class));

        $this->getEngine()
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): \Teknoo\East\Foundation\Template\EngineInterface {
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
                $this->createMock(ManagerInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallback(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(CallbackStreamInterface::class);
        $stream->method('bind')->willReturnCallback(
            function (callable $callback) use ($stream): \PHPUnit\Framework\MockObject\MockObject {
                $callback();
                return $stream;
            }
        );

        $this->getStreamFactory()
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): \Teknoo\East\Foundation\Template\EngineInterface {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
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
                $this->createMock(ManagerInterface::class)
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
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo', $code);

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);

        $this->getStreamFactory()
            ->method('createStream')
            ->willReturn($stream);

        $this->getEngine()
            ->method('render')
            ->willReturnCallback(
                function (PromiseInterface $promise): \Teknoo\East\Foundation\Template\EngineInterface {
                    $promise->success(
                        $this->createMock(ResultInterface::class)
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
                $this->createMock(ManagerInterface::class)
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

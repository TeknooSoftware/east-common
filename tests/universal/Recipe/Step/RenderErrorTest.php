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
 * @license     https://teknoo.software/license/mit         MIT License
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

    public function testInvokeBadRequest()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            $this->createMock(Throwable::class)
        );
    }

    public function testInvokeBadClient()
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

    public function testInvokeBadErrorTemplate()
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

    public function testInvokeBadError()
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

    public function testInvokeNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

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

    public function testInvokeWithMessage()
    {
        $message = $this->createMock(MessageInterface::class);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

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

    public function testInvokeError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->exactly(2))
            ->method('errorInRequest');

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

    public function testInvokeWithStreamCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo');

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

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
                $callback();
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
                    $promise->success(
                        $this->createMock(ResultInterface::class)
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
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

    public static function codeProvider(): array
    {
        return [
            [400],
            [401],
            [402],
            [403],
            [404],
            [500],
            [550],
        ];
    }

    #[DataProvider('codeProvider')]
    public function testInvokeWithCode(int $code)
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $template = 'foo';
        $error = new Exception('foo', $code);

        $client->expects($this->once())
            ->method('errorInRequest')
            ->with($error, true);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);

        $this->getStreamFactory()
            ->expects($this->any())
            ->method('createStream')
            ->willReturn($stream);

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

    public function testSetTidyConfig()
    {
        self::assertInstanceOf(
            RenderError::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

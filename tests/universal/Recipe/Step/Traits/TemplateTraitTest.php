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

namespace Teknoo\Tests\East\Common\Recipe\Step\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Contracts\Rendering\LiveComponentBuilderInterface;
use Teknoo\East\Common\Recipe\Step\Traits\TemplateTrait;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(TemplateTrait::class)]
class TemplateTraitTest extends TestCase
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

    private function buildMockObject(): object
    {
        return new class (
            $this->getEngine(true),
            $this->getStreamFactory(true),
            $this->getResponseFactory(true)
        ) {
            use TemplateTrait;

            public function __construct(
                EngineInterface $templating,
                StreamFactoryInterface $streamFactory,
                ResponseFactoryInterface $responseFactory,
            ) {
                $this->templating = $templating;
                $this->streamFactory = $streamFactory;
                $this->responseFactory = $responseFactory;
            }

            public function callRender(
                ClientInterface $client,
                string $view,
                array $parameters = [],
                int $status = 200,
                array $headers = [],
                ?string $api = null,
                bool $cleanHtml = false,
                ?MessageInterface $message = null,
            ): void {
                $this->render(
                    client: $client,
                    view: $view,
                    parameters: $parameters,
                    status: $status,
                    headers: $headers,
                    api: $api,
                    cleanHtml: $cleanHtml,
                    message: $message,
                );
            }
        };
    }

    public function testSetLiveComponentBuilder(): void
    {
        $liveComponentBuilder = $this->createStub(LiveComponentBuilderInterface::class);
        $object = $this->buildMockObject();

        $result = $object->setLiveComponentBuilder($liveComponentBuilder);

        $this->assertSame($object, $result);
    }

    public function testSetLiveComponentBuilderWithNull(): void
    {
        $object = $this->buildMockObject();

        $result = $object->setLiveComponentBuilder(null);

        $this->assertSame($object, $result);
    }

    public function testRenderWithoutLiveComponentBuilder(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([]);

        $object = $this->buildMockObject();

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderButNotServerRequest(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->never())->method('buildComponent');

        $message = $this->createStub(MessageInterface::class);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderButMissingAttributes(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->never())->method('buildComponent');

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([]);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderButMissingLiveComponent(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->never())->method('buildComponent');

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([
            '_live_parameters' => ['other' => 'value'],
            '_live_body' => [],
        ]);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderButMissingLiveBody(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->never())->method('buildComponent');

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([
            '_live_parameters' => ['_live_component' => 'TestComponent'],
        ]);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderAndValidAttributesReturnsFalse(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');
        $client->expects($this->never())->method('sendAResponseIsOptional');

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
                    $promise->success($this->createStub(ResultInterface::class));
                    return $this->getEngine();
                }
            );

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->once())
            ->method('buildComponent')
            ->willReturn(false);

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([
            '_live_parameters' => ['_live_component' => 'TestComponent'],
            '_live_body' => ['props' => []],
        ]);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderAndValidAttributesReturnsTrue(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())->method('acceptResponse');
        $client->expects($this->once())->method('sendAResponseIsOptional');

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->once())
            ->method('buildComponent')
            ->with(
                $this->isInstanceOf(ServerRequestInterface::class),
                'TestComponent',
                ['key' => 'value'],
                ['props' => ['test' => 'data']]
            )
            ->willReturn(true);

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([
            '_live_parameters' => ['_live_component' => 'TestComponent'],
            '_live_body' => ['props' => ['test' => 'data']],
        ]);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }

    public function testRenderWithLiveComponentBuilderWithCallbackStream(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->never())->method('acceptResponse');
        $client->expects($this->once())->method('sendAResponseIsOptional');

        $liveComponentBuilder = $this->createMock(LiveComponentBuilderInterface::class);
        $liveComponentBuilder->expects($this->once())
            ->method('buildComponent')
            ->willReturn(true);

        $message = $this->createStub(ServerRequestInterface::class);
        $message->method('getAttributes')->willReturn([
            '_live_parameters' => ['_live_component' => 'TestComponent'],
            '_live_body' => ['props' => []],
        ]);

        $stream = $this->createStub(CallbackStreamInterface::class);

        $this->getStreamFactory(true)
            ->method('createStream')
            ->willReturn($stream);

        $object = $this->buildMockObject();
        $object->setLiveComponentBuilder($liveComponentBuilder);

        $object->callRender(
            client: $client,
            view: 'test.html.twig',
            parameters: ['key' => 'value'],
            message: $message,
        );
    }
}

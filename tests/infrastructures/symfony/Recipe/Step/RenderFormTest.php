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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RenderForm::class)]
class RenderFormTest extends TestCase
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

    public function buildStep(): RenderForm
    {
        return new RenderForm(
            $this->getEngine(),
            $this->getStreamFactory(),
            $this->getResponseFactory()
        );
    }

    public function testInvokeNonCallback(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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
            RenderForm::class,
            $this->buildStep()(
                $request,
                $client,
                $form,
                'foo',
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }


    public function testInvokeNonCallbackWithFormErrorInApi(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);

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
            RenderForm::class,
            $this->buildStep()(
                request: $request,
                client: $client,
                form: $form,
                template: 'foo',
                object: $this->createMock(IdentifiedObjectInterface::class),
                api: 'json'
            )
        );
    }

    public function testInvokeWithSavedObjectFlag(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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
            RenderForm::class,
            $this->buildStep()(
                request: $request,
                client: $client,
                form: $form,
                template: 'foo',
                object: $this->createMock(IdentifiedObjectInterface::class),
                objectSaved: true,
            )
        );
    }

    public function testInvokeWithTimestampableAndNonCallback(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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

        $object = new class () implements IdentifiedObjectInterface, PublishableInterface {
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
        };

        $this->assertInstanceOf(
            RenderForm::class,
            $this->buildStep()(
                $request,
                $client,
                $form,
                'foo',
                $object,
                true
            )
        );
    }

    public function testInvokeError(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('errorInRequest');

        $form = $this->createMock(FormInterface::class);

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
                        new \Exception('foo')
                    );

                    return $this->getEngine();
                }
            );

        $this->assertInstanceOf(
            RenderForm::class,
            $this->buildStep()(
                $request,
                $client,
                $form,
                'foo',
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testInvokeWithStreamCallback(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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
            RenderForm::class,
            $this->buildStep()(
                $request,
                $client,
                $form,
                'foo',
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }

    public function testSetTidyConfig(): void
    {
        $this->assertInstanceOf(
            RenderForm::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

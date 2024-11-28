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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

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
 * @license     https://teknoo.software/license/mit         MIT License
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

    public function testInvokeNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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


    public function testInvokeNonCallbackWithFormErrorInApi()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())->method('isSubmitted')->willReturn(true);
        $form->expects($this->any())->method('isValid')->willReturn(false);

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

    public function testInvokeWithSavedObjectFlag()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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

    public function testInvokeWithTimestampableAndNonCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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

        $object = new class implements IdentifiedObjectInterface, PublishableInterface {
            public function getId(): string
            {
                return 123;
            }

            public function getPublishedAt(): ?\DateTimeInterface
            {
                return new \DateTimeImmutable('2021-01-21');
            }

            public function setPublishedAt(\DateTimeInterface $dateTime): PublishableInterface
            {
                return $this;
            }
        };

        self::assertInstanceOf(
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

    public function testInvokeError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('errorInRequest');

        $form = $this->createMock(FormInterface::class);

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
                        new \Exception('foo')
                    );

                    return $this->getEngine();
                }
            );

        self::assertInstanceOf(
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

    public function testInvokeWithStreamCallback()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn([]);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $form = $this->createMock(FormInterface::class);

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

    public function testSetTidyConfig()
    {
        self::assertInstanceOf(
            RenderForm::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

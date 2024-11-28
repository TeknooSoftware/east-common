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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use stdClass;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Common\Recipe\Step\RenderList;
use TypeError;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RenderList::class)]
class RenderListTest extends TestCase
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

    public function buildStep(): RenderList
    {
        return new RenderList($this->getEngine(), $this->getStreamFactory(), $this->getResponseFactory());
    }

    public function testInvokeBadClient()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new stdClass(),
            [],
            1,
            2,
            3,
            'foo'
        );
    }

    public function testInvokeBadRequest()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            new stdClass(),
            $this->createMock(ClientInterface::class),
            [],
            1,
            2,
            3,
            'foo'
        );
    }

    public function testInvokeBadObjectsCollections()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new stdClass(),
            1,
            2,
            3,
            'foo'
        );
    }

    public function testInvokeBadPageCount()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            [],
            new stdClass(),
            2,
            3,
            'foo'
        );
    }

    public function testInvokeBadItemsPerPages()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            [],
            1,
            new stdClass(),
            3,
            'foo'
        );
    }

    public function testInvokeBadPage()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            [],
            1,
            2,
            new stdClass(),
            'foo'
        );
    }

    public function testInvokeBadTemplate()
    {
        $this->expectException(TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            [],
            1,
            2,
            3,
            new stdClass()
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
            RenderList::class,
            $this->buildStep()(
                $request,
                $client,
                [],
                1,
                2,
                3,
                'foo'
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
            RenderList::class,
            $this->buildStep()(
                $request,
                $client,
                [],
                1,
                2,
                3,
                'foo'
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
            RenderList::class,
            $this->buildStep()(
                $request,
                $client,
                [],
                1,
                2,
                3,
                'foo'
            )
        );
    }

    public function testSetTidyConfig()
    {
        self::assertInstanceOf(
            RenderList::class,
            $this->buildStep()->setTidyConfig(['foo' => 'bar']),
        );
    }
}

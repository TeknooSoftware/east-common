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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Common\Recipe\Step\SendMedia;
use Teknoo\East\Foundation\Client\ClientInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SendMedia::class)]
class SendMediaTest extends TestCase
{
    private (ResponseFactoryInterface&Stub)|(ResponseFactoryInterface&MockObject)|null $responseFactory = null;

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

    public function buildStep(): SendMedia
    {
        return new SendMedia($this->getResponseFactory(true));
    }

    public function testInvokeBadClient(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createStub(Media::class),
            $this->createStub(StreamInterface::class)
        );
    }

    public function testInvokeBadMedia(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(ClientInterface::class),
            new \stdClass(),
            $this->createStub(StreamInterface::class)
        );
    }

    public function testInvokeBadStream(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(ClientInterface::class),
            $this->createStub(Media::class),
            new \stdClass()
        );
    }

    public function testInvokeWithMetadata(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $media = $this->createStub(Media::class);
        $media->method('getMetadata')->willReturn(
            $this->createStub(MediaMetadata::class)
        );

        $stream = $this->createsTUB(StreamInterface::class);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->assertInstanceOf(
            SendMedia::class,
            $this->buildStep()(
                $client,
                $media,
                $stream
            )
        );
    }

    public function testInvokeWithoutMetadata(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('acceptResponse');

        $media = $this->createStub(Media::class);
        $media->method('getMetadata')->willReturn(null);

        $stream = $this->createStub(StreamInterface::class);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->assertInstanceOf(
            SendMedia::class,
            $this->buildStep()(
                $client,
                $media,
                $stream
            )
        );
    }
}

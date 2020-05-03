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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\EndPoint;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\StaticEndPointTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\StaticEndPointTrait
 */
class StaticEndPointTraitTest extends TestCase
{
    public function buildEndPoint()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        $endPoint = new class implements EndPointInterface {
            use EastEndPointTrait;
            use StaticEndPointTrait;

            /**
             * {@inheritdoc}
             */
            public function render(ClientInterface $client, string $view, array $parameters = array(), int $status = 200, array $headers = []): EndPointInterface
            {
                $client->acceptResponse(new TextResponse($view.':executed'));
                return $this;
            }
        };

        $endPoint->setResponseFactory($responseFactory);
        $endPoint->setStreamFactory($streamFactory);

        return $endPoint;
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(new \stdClass(), 'fooBar');
    }

    public function testInvokeBadTemplate()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testInvoke()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    return 'fooBar:executed' == (string) $value->getBody();
                }

                return false;
            }))
            ->willReturnSelf();

        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, 'fooBar')
        );
    }
}

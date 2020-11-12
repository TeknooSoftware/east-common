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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Query\Content\PublishedContentFromSlugQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\ContentEndPointTrait
 */
class ContentEndPointTraitTest extends TestCase
{
    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @return ContentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getContentLoader(): ContentLoader
    {
        if (!$this->contentLoader instanceof ContentLoader) {
            $this->contentLoader = $this->createMock(ContentLoader::class);
        }

        return $this->contentLoader;
    }

    public function buildEndPoint()
    {
        $contentLoader = $this->getContentLoader();

        $endPoint = new class($contentLoader, 'error-404') implements EndPointInterface {
            use EastEndPointTrait;
            use ContentEndPointTrait;

            /**
             * {@inheritdoc}
             */
            public function render(ClientInterface $client, string $view, array $parameters = array(), int $status = 200, array $headers = []): EndPointInterface
            {
                if ('error-404' != $view) {
                    if (!isset($parameters['content'])) {
                        throw new \Exception('missing content key in view parameters');
                    }

                    if (!isset($parameters['foo']) || 'bar' != $parameters['foo']) {
                        throw new \Exception('missing foo key in view parameters');
                    }

                    if (empty($headers['Last-Modified'])) {
                        throw new \Exception('missing Last Modified in header');
                    }
                }

                $client->acceptResponse(new TextResponse($view.':executed'));
                return $this;
            }
        };
        
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        $endPoint->setResponseFactory($responseFactory);
        $endPoint->setStreamFactory($streamFactory);

        return $endPoint;
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(new \stdClass(), $this->createMock(ServerRequestInterface::class));
    }

    public function testInvokeBadServerRequest()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeNotFound()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    $result = 'error-404:executed' == (string) $value->getBody();
                    return $result;
                }

                return false;
            }))
            ->willReturnSelf();

        $client->expects(self::never())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromSlugQuery('foo-bar'))
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $promise->fail(new \DomainException());

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn('/item/sub/foo-bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn($uri);

        $request->expects(self::any())
            ->method('getAttribute')
            ->willReturnMap([
                [ViewParameterInterface::REQUEST_PARAMETER_KEY, [], ['foo'=>'bar']]
            ]);


        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeOtherError()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())
            ->method('acceptResponse');

        $client->expects(self::once())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromSlugQuery('foo-bar'))
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $promise->fail(new \Exception());

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn('/item/sub/foo-bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn($uri);

        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeFoundWrongType()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())
            ->method('acceptResponse');

        $client->expects(self::once())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromSlugQuery('foo-bar'))
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $content->setUpdatedAt(new \DateTime('2019-12-30'));
                $promise->success($content);

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn('/item/sub/foo-bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn($uri);

        $request->expects(self::any())
            ->method('getAttribute')
            ->willReturnMap([
                [ViewParameterInterface::REQUEST_PARAMETER_KEY, [], ['foo'=>'bar']]
            ]);

        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeFound($pathPrefix='')
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    $result = 'fooBar:executed' == (string) $value->getBody();
                    return $result;
                }

                return false;
            }))
            ->willReturnSelf();

        $client->expects(self::never())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromSlugQuery('foo-bar'))
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $type = new Type();
                $type->setTemplate('fooBar');
                $content->setType($type);
                $content->setUpdatedAt(new \DateTime('2019-12-30'));
                $promise->success($content);

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn($pathPrefix . '/item/sub/foo-bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn($uri);

        $request->expects(self::any())
            ->method('getAttribute')
            ->willReturnMap([
                [ViewParameterInterface::REQUEST_PARAMETER_KEY, [], ['foo'=>'bar']]
            ]);

        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeDefault($pathPrefix='')
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

        $client->expects(self::never())
            ->method('errorInRequest')
            ->willReturnSelf();

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromSlugQuery('default'))
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $type = new Type();
                $type->setTemplate('fooBar');
                $content->setType($type);
                $content->setUpdatedAt(new \DateTime('2019-12-30'));
                $promise->success($content);

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn($pathPrefix . '/');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn($uri);

        $request->expects(self::any())
            ->method('getAttribute')
            ->willReturnMap([
                [ViewParameterInterface::REQUEST_PARAMETER_KEY, [], ['foo'=>'bar']]
            ]);

        self::assertInstanceOf(
            RenderingInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }
}

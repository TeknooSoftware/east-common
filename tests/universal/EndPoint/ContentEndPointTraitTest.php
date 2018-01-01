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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\EndPoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Zend\Diactoros\Response\TextResponse;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\ContentEndPointTrait
 */
class ContentEndPointTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @return ContentLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentLoader(): ContentLoader
    {
        if (!$this->contentLoader instanceof ContentLoader) {
            $this->contentLoader = $this->createMock(ContentLoader::class);
        }

        return $this->contentLoader;
    }

    /**
     * @return EndPointInterface
     */
    public function buildEndPoint(): EndPointInterface
    {
        $contentLoader = $this->getContentLoader();

        return new class($contentLoader, 'error-404') implements EndPointInterface {
            use EastEndPointTrait;
            use ContentEndPointTrait;

            /**
             * {@inheritdoc}
             */
            public function render(ClientInterface $client, string $view, array $parameters = array(), int $status = 200): EndPointInterface
            {
                if ('error-404' != $view) {
                    if (!isset($parameters['content'])) {
                        throw new \Exception('missing content key in view parameters');
                    }

                    if (!isset($parameters['foo']) || 'bar' != $parameters['foo']) {
                        throw new \Exception('missing foo key in view parameters');
                    }
                }

                $client->acceptResponse(new TextResponse($view.':executed'));
                return $this;
            }
        };
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvokeBadClient()
    {
        $this->buildEndPoint()(new \stdClass(), $this->createMock(ServerRequestInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testInvokeBadServerRequest()
    {
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
            ->method('bySlug')
            ->with('foo-bar')
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
            EndPointInterface::class,
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
            ->method('bySlug')
            ->with('foo-bar')
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
            EndPointInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeFound()
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
            ->method('bySlug')
            ->with('foo-bar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $type = new Type();
                $type->setTemplate('fooBar');
                $content->setType($type);
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
            EndPointInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }

    public function testInvokeDefault()
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
            ->method('bySlug')
            ->with('default')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $type = new Type();
                $type->setTemplate('fooBar');
                $content->setType($type);
                $promise->success($content);

                return $this->getContentLoader();
            });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects(self::any())
            ->method('getPath')
            ->willReturn('/');

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
            EndPointInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }
}

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
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Service\MenuGenerator;
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
     * @var MenuGenerator
     */
    private $menuGenerator;

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
     * @return MenuGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMenuGenerator(): MenuGenerator
    {
        if (!$this->menuGenerator instanceof MenuGenerator) {
            $this->menuGenerator = $this->createMock(MenuGenerator::class);
        }

        return $this->menuGenerator;
    }

    /**
     * @return EndPointInterface
     */
    public function buildEndPoint(): EndPointInterface
    {
        $contentLoader = $this->getContentLoader();
        $menuGenerator = $this->getMenuGenerator();
        return new class ($contentLoader, $menuGenerator) implements EndPointInterface
        {
            use EastEndPointTrait;
            use ContentEndPointTrait;

            /**
             * {@inheritdoc}
             */
            public function render(ClientInterface $client, string $view, array $parameters = array()): EndPointInterface
            {
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
                $promise->fail(new \DomainException());

                return $this->getContentLoader();
            });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn('/category/sub/foo-bar');

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
            ->with('foo-bar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $content = new Content();
                $type = new Type();
                $type->setTemplate('fooBar');
                $content->setType($type);
                $promise->success($content);

                return $this->getContentLoader();
            });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn('/category/sub/foo-bar');

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

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getUri')
            ->willReturn('/');

        self::assertInstanceOf(
            EndPointInterface::class,
            $this->buildEndPoint()($client, $request)
        );
    }
}
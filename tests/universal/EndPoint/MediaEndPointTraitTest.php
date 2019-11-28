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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\EndPoint;

use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\MediaEndPointTrait
 */
class MediaEndPointTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MediaLoader
     */
    private $mediaLoader;

    /**
     * @return MediaLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMediaLoader(): MediaLoader
    {
        if (!$this->mediaLoader instanceof MediaLoader) {
            $this->mediaLoader = $this->createMock(MediaLoader::class);
        }

        return $this->mediaLoader;
    }

    /**
     * @return EndPointInterface
     */
    public function buildEndPoint(): EndPointInterface
    {
        $mediaLoader = $this->getMediaLoader();
        return new class($mediaLoader) implements EndPointInterface {
            use EastEndPointTrait;
            use MediaEndPointTrait;
        };
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(new \stdClass(), 'fooBar');
    }

    public function testInvokeBadId()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeFound()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    return 'fooBarContent' == (string) $value->getBody();
                }

                return false;
            }))
            ->willReturnSelf();

        $client->expects(self::never())->method('errorInRequest');

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->with('fooBar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $media = new Media();
                $media->setFile(new class extends \MongoGridFSFile {
                    public function getSize()
                    {
                        return 10;
                    }

                    public function getResource()
                    {
                        $hf = fopen('php://memory', 'rw+');
                        fwrite($hf, 'fooBarContent');
                        return $hf;
                    }
                });
                $promise->success($media);

                return $this->getMediaLoader();
            });

        self::assertInstanceOf(
            EndPointInterface::class,
            $this->buildEndPoint()($client, 'fooBar')
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

        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->with('fooBar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $promise->fail(new \DomainException());

                return $this->getMediaLoader();
            });

        self::assertInstanceOf(
            EndPointInterface::class,
            $this->buildEndPoint()($client, 'fooBar')
        );
    }
}

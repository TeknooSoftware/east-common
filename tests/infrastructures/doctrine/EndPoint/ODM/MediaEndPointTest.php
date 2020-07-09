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

namespace Teknoo\Tests\East\Website\Doctrine\EndPoint\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\Media as BaseMedia;
use Teknoo\East\Website\Doctrine\EndPoint\ODM\MediaEndPoint;
use Teknoo\Tests\East\Website\EndPoint\MediaEndPointTraitTest;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Doctrine\EndPoint\ODM\MediaEndPoint
 */
class MediaEndPointTest extends MediaEndPointTraitTest
{
    public function buildEndPoint(): MediaEndPoint
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $inStream = null;
        $response->expects(self::any())->method('withBody')->willReturnCallback(
            function ($value) use (&$inStream, $response) {
                $inStream = $value;
                return $response;
            }
        );
        $response->expects(self::any())->method('getBody')->willReturnCallback(
            function () use (&$inStream) {
                return $inStream;
            }
        );
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $endPoint = new MediaEndPoint($this->getMediaLoader(), new CallbackStreamFactory());
        $endPoint->setResponseFactory($responseFactory);

        $repository = $this->createMock(GridFSRepository::class);
        $repository->expects(self::any())->method('openDownloadStream')
            ->willReturnCallback(
                static function () {
                    $hf = fopen('php://memory', 'rw+');
                    fwrite($hf, 'fooBarContent');
                    fseek($hf, 0);
                    return $hf;
                }
            );

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects(self::any())->method('getRepository')->willReturn($repository);
        $endPoint->registerRepository($dm);

        return $endPoint;
    }

    public function testRegisterRepositoryWithNonGridFSRepository()
    {
        $this->expectException(\RuntimeException::class);

        $repository = $this->createMock(DocumentRepository::class);

        $dm = $this->createMock(DocumentManager::class);
        $dm->expects(self::any())->method('getRepository')->willReturn($repository);

        $endPoint = $this->buildEndPoint();
        $endPoint->registerRepository($dm);
    }

    public function testGetSourceWithNonGridFSMedia()
    {
        $this->getMediaLoader()
            ->expects(self::any())
            ->method('load')
            ->with('fooBar')
            ->willReturnCallback(function ($id, PromiseInterface $promise) {
                $media = new class extends BaseMedia {

                };

                $promise->success($media);

                return $this->getMediaLoader();
            });

        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $endPoint = $this->buildEndPoint();

        $class = \get_class($endPoint);
        self::assertInstanceOf(
            $class,
            $endPoint($client, 'fooBar')
        );
    }
}

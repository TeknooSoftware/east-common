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

namespace Teknoo\Tests\East\WebsiteBundle\EndPoint;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Diactoros\CallbackStreamFactory;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\WebsiteBundle\EndPoint\MediaEndPoint;
use Teknoo\Tests\East\Website\EndPoint\MediaEndPointTraitTest;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\EndPoint\MediaEndPoint
 */
class MediaEndPointTest extends MediaEndPointTraitTest
{
    public function buildEndPoint(): EndPointInterface
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

        $endPoint = new MediaEndPoint($this->getMediaLoader());
        $endPoint->setResponseFactory($responseFactory);
        $endPoint->setStreamFactory(new CallbackStreamFactory());

        return $endPoint;
    }
}

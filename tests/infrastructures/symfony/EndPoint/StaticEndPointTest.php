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
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\WebsiteBundle\EndPoint\StaticEndPoint;
use Teknoo\Tests\East\Website\EndPoint\StaticEndPointTraitTest;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\EndPoint\StaticEndPoint
 */
class StaticEndPointTest extends StaticEndPointTraitTest
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @return EngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTemplating(): EngineInterface
    {
        if (!$this->templating instanceof EngineInterface) {
            $this->templating = $this->createMock(EngineInterface::class);

            $this->templating->expects(self::any())
                ->method('render')
                ->willReturn('fooBar:executed');
        }

        return $this->templating;
    }

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

        $stream = new CallbackStream(function () {});
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        return (new StaticEndPoint())
            ->setTemplating($this->getTemplating())
            ->setResponseFactory($responseFactory)
            ->setStreamFactory($streamFactory);
    }
}

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

namespace Teknoo\Tests\East\WebsiteBundle\EndPoint;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Diactoros\CallbackStream;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\WebsiteBundle\EndPoint\ContentEndPoint;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Tests\East\Website\EndPoint\ContentEndPointTraitTest;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\EndPoint\ContentEndPoint
 */
class ContentEndPointTest extends ContentEndPointTraitTest
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @return EngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTemplating(): EngineInterface
    {
        if (!$this->templating instanceof EngineInterface) {
            $this->templating = $this->createMock(EngineInterface::class);

            $this->templating
                ->expects(self::any())
                ->method('render')
                ->willReturnCallback(function (PromiseInterface $promise, $script) {
                    $result = $this->createMock(ResultInterface::class);
                    $result->expects(self::any())->method('__toString')->willReturn($script.':executed');
                    $promise->success($result);

                    return $this->templating;
                });
        }

        return $this->templating;
    }

    public function buildEndPoint(): ContentEndPoint
    {
        $endPoint = (new ContentEndPoint($this->getContentLoader(), 'error-404'))
            ->setTemplating($this->getTemplating());

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

        $stream = new CallbackStream(function () {
        });
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        $endPoint->setResponseFactory($responseFactory);
        $endPoint->setStreamFactory($streamFactory);

        return $endPoint;
    }

    public function testInvokeFoundFromAppFile()
    {
        $this->testInvokeFound('/app.php');
    }

    public function testInvokeFoundFromAppDevFile()
    {
        $this->testInvokeFound('/app_dev.php');
    }

    public function testInvokeFoundFromIndexFile()
    {
        $this->testInvokeFound('/index.php');
    }

    public function testInvokeDefaultFromAppFile()
    {
        $this->testInvokeDefault('/app.php');
    }

    public function testInvokeDefaultFromAppDevFile()
    {
        $this->testInvokeDefault('/app_dev.php');
    }

    public function testInvokeDefaultFromIndexFile()
    {
        $this->testInvokeDefault('/index.php');
    }
}

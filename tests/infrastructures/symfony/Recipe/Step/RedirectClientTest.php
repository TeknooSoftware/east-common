<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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

namespace Teknoo\Tests\East\WebsiteBundle\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\WebsiteBundle\Recipe\Step\RedirectClient;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Recipe\Step\RedirectClient
 */
class RedirectClientTest extends TestCase
{
    private ?UrlGeneratorInterface $router = null;

    private ?ResponseFactoryInterface $responseFactory = null;

    /**
     * @return UrlGeneratorInterface|MockObject
     */
    private function getUrlGenerator(): UrlGeneratorInterface
    {
        if (!$this->router instanceof UrlGeneratorInterface) {
            $this->router = $this->createMock(UrlGeneratorInterface::class);
        }

        return $this->router;
    }

    /**
     * @return ResponseFactoryInterface|MockObject
     */
    private function getResponseFactory(): ResponseFactoryInterface
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        }

        return $this->responseFactory;
    }

    public function buildStep(): RedirectClient
    {
        return new RedirectClient(
            $this->getResponseFactory(),
            $this->getUrlGenerator()
        );
    }

    public function testInvoke()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getUrlGenerator()
            ->expects(self::any())
            ->method('generate')
            ->willReturn('bar');

        self::assertInstanceOf(
            RedirectClient::class,
            $this->buildStep()(
                $this->createMock(ManagerInterface::class),
                $this->createMock(ClientInterface::class),
                'foo',
                301,
                ['foo' => 'bar']
            )
        );
    }
}

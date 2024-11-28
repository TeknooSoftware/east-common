<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\RedirectClient;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RedirectClient::class)]
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
        $response->expects($this->any())->method('withHeader')->willReturnSelf();
        $response->expects($this->any())->method('withBody')->willReturnSelf();
        $this->getResponseFactory()
            ->expects($this->any())
            ->method('createResponse')
            ->willReturn($response);

        $this->getUrlGenerator()
            ->expects($this->any())
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

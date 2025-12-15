<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\RedirectClient;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RedirectClient::class)]
class RedirectClientTest extends TestCase
{
    private (UrlGeneratorInterface&Stub)|(UrlGeneratorInterface&MockObject)|null $router = null;

    private (ResponseFactoryInterface&Stub)|(ResponseFactoryInterface&MockObject)|null $responseFactory = null;

    private function getUrlGenerator(bool $stub = false): (UrlGeneratorInterface&Stub)|(UrlGeneratorInterface&MockObject)
    {
        if (!$this->router instanceof UrlGeneratorInterface) {
            if ($stub) {
                $this->router = $this->createStub(UrlGeneratorInterface::class);
            } else {
                $this->router = $this->createMock(UrlGeneratorInterface::class);
            }
        }

        return $this->router;
    }

    private function getResponseFactory(bool $stub = false): (ResponseFactoryInterface&Stub)|(ResponseFactoryInterface&MockObject)
    {
        if (!$this->responseFactory instanceof ResponseFactoryInterface) {
            if ($stub) {
                $this->responseFactory = $this->createStub(ResponseFactoryInterface::class);
            } else {
                $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
            }
        }

        return $this->responseFactory;
    }

    public function buildStep(): RedirectClient
    {
        return new RedirectClient(
            $this->getResponseFactory(true),
            $this->getUrlGenerator(true)
        );
    }

    public function testInvoke(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('withHeader')->willReturnSelf();
        $response->method('withBody')->willReturnSelf();
        $this->getResponseFactory(true)
            ->method('createResponse')
            ->willReturn($response);

        $this->getUrlGenerator(true)
            ->method('generate')
            ->willReturn('bar');

        $this->assertInstanceOf(
            RedirectClient::class,
            $this->buildStep()(
                $this->createStub(ManagerInterface::class),
                $this->createStub(ClientInterface::class),
                'foo',
                301,
                ['foo' => 'bar']
            )
        );
    }
}

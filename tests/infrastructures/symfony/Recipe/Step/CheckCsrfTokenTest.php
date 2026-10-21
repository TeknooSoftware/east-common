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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\CheckCsrfToken;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CheckCsrfToken::class)]
class CheckCsrfTokenTest extends TestCase
{
    private (CsrfTokenManagerInterface&MockObject)|null $csrfTokenManager = null;

    private function getCsrfTokenManager(): CsrfTokenManagerInterface&MockObject
    {
        if (null === $this->csrfTokenManager) {
            $this->csrfTokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        }

        return $this->csrfTokenManager;
    }

    public function buildStep(): CheckCsrfToken
    {
        return new CheckCsrfToken(
            $this->getCsrfTokenManager(),
        );
    }

    private function buildRequest(mixed $body = null, array $query = []): ServerRequestInterface
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getParsedBody')->willReturn($body);
        $request->method('getQueryParams')->willReturn($query);

        return $request;
    }

    public function testInvokeWithoutTokenId(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->never())
            ->method('isTokenValid');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(),
                $manager,
            )
        );
    }

    public function testInvokeWithEmptyTokenId(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->never())
            ->method('isTokenValid');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(),
                $manager,
                '',
            )
        );
    }

    public function testInvokeWithoutTokenManager(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(
                static fn ($error): bool => $error instanceof RuntimeException && 500 === $error->getCode()
            ));

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            new CheckCsrfToken(null)(
                $this->buildRequest(query: ['_token' => 'bar']),
                $manager,
                'foo',
            )
        );
    }

    public function testInvokeWithValidTokenInQuery(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(new CsrfToken('foo', 'bar'))
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(query: ['_token' => 'bar']),
                $manager,
                'foo',
            )
        );
    }

    public function testInvokeWithValidTokenInBody(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(new CsrfToken('foo', 'bar'))
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(body: ['_token' => 'bar'], query: ['_token' => 'baz']),
                $manager,
                'foo',
            )
        );
    }

    public function testInvokeWithValidTokenInCustomField(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->once())
            ->method('isTokenValid')
            ->with(new CsrfToken('foo', 'bar'))
            ->willReturn(true);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('error');

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(query: ['_csrf' => 'bar']),
                $manager,
                'foo',
                '_csrf',
            )
        );
    }

    public function testInvokeWithInvalidToken(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->once())
            ->method('isTokenValid')
            ->willReturn(false);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(
                static fn ($error): bool => $error instanceof DomainException && 403 === $error->getCode()
            ));

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(query: ['_token' => 'bar']),
                $manager,
                'foo',
            )
        );
    }

    public function testInvokeWithMissingToken(): void
    {
        $this->getCsrfTokenManager()
            ->expects($this->never())
            ->method('isTokenValid');

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('error')
            ->with($this->callback(
                static fn ($error): bool => $error instanceof DomainException && 403 === $error->getCode()
            ));

        $this->assertInstanceOf(
            CheckCsrfToken::class,
            $this->buildStep()(
                $this->buildRequest(body: ['_token' => ['bar']]),
                $manager,
                'foo',
            )
        );
    }
}

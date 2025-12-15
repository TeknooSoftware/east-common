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

namespace Teknoo\Tests\East\Common\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Foundation\Session\SessionInterface;
use Teknoo\East\Common\Middleware\LocaleMiddleware;
use Teknoo\East\Common\View\ParametersBag;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LocaleMiddleware::class)]
class LocaleMiddlewareTest extends TestCase
{
    private ?object $translatableSetter = null;

    public function getTranslatableSetter(): callable
    {
        if ($this->translatableSetter === null) {
            $this->translatableSetter = new class () {
                public $lang;

                public function __invoke($lang): void
                {
                    $this->lang = $lang;
                }
            };
        }

        return $this->translatableSetter;
    }

    /**
     * @param string $locale
     */
    public function buildMiddleware($locale = 'en'): LocaleMiddleware
    {
        return new LocaleMiddleware(
            $this->getTranslatableSetter(true),
            $locale,
        );
    }

    public function testWithMessage(): void
    {
        $message = $this->createStub(MessageInterface::class);

        $manager = $this->createStub(ManagerInterface::class);

        $bag = $this->createStub(ParametersBag::class);


        $sessionMiddleware = $this->createStub(SessionInterface::class);
        $sessionMiddleware
            ->method('get')
            ->willReturnCallback(function (string $key, PromiseInterface $promise) use ($sessionMiddleware): MockObject {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($message, $manager, $bag)
        );

        $this->assertEquals(
            'en',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoInRequestNoInSession(): void
    {
        $bag = $this->createStub(ParametersBag::class);

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createStub(ServerRequestInterface::class);

        $manager = $this->createStub(ManagerInterface::class);

        $serverRequest->expects($this->once())
            ->method('withAttribute')
            ->with('locale', 'en')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createStub(SessionInterface::class);
        $sessionMiddleware
            ->method('get')
            ->willReturnCallback(function (string $key, PromiseInterface $promise) use ($sessionMiddleware): Stub {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        $serverRequest
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal
            ->method('getAttribute')
            ->willReturn([]);

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        $this->assertEquals(
            'en',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoInRequestInSession(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createStub(ServerRequestInterface::class);
        $bag = $this->createStub(ParametersBag::class);

        $manager = $this->createStub(ManagerInterface::class);

        $serverRequest->expects($this->once())
            ->method('withAttribute')
            ->with('locale', 'fr')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createStub(SessionInterface::class);
        $sessionMiddleware
            ->method('get')
            ->willReturnCallback(function (string $key, PromiseInterface $promise) use ($sessionMiddleware): \PHPUnit\Framework\MockObject\Stub {
                $promise->success('fr');

                return $sessionMiddleware;
            });

        $serverRequest
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal
            ->method('getAttribute')
            ->willReturn(['foo' => 'bar']);

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        $this->assertEquals(
            'fr',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteInRequestInSession(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createStub(ServerRequestInterface::class);

        $bag = $this->createStub(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())
            ->method('updateMessage')
            ->with($serverRequestFinal)
            ->willReturnSelf();

        $serverRequest->expects($this->once())
            ->method('withAttribute')
            ->with('locale', 'es')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects($this->never())->method('get');
        $sessionMiddleware->expects($this->once())->method('set')->with('locale', 'es');

        $serverRequest
            ->method('getQueryParams')
            ->willReturn(['locale' => 'es']);

        $serverRequest
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal
            ->method('getAttribute')
            ->willReturn([]);

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        $this->assertEquals(
            'es',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoSession(): void
    {
        $serverRequest = $this->createStub(ServerRequestInterface::class);
        $serverRequestFinal = $this->createStub(ServerRequestInterface::class);

        $bag = $this->createStub(ParametersBag::class);

        $manager = $this->createStub(ManagerInterface::class);

        $serverRequest
            ->method('getQueryParams')
            ->willReturn([]);

        $serverRequestFinal
            ->method('getAttribute')
            ->willReturn([]);

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }
}

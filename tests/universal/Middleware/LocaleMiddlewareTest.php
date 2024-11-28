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

namespace Teknoo\Tests\East\Common\Middleware;

use PHPUnit\Framework\Attributes\CoversClass;
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LocaleMiddleware::class)]
class LocaleMiddlewareTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $translatableSetter;

    public function getTranslatableSetter(): callable
    {
        if (!$this->translatableSetter) {
            $this->translatableSetter = new class () {
                public $lang;
                public function __invoke($lang): void {
                    $this->lang = $lang;
                }
            };
        }

        return $this->translatableSetter;
    }

    /**
     * @param string $locale
     * @return LocaleMiddleware
     */
    public function buildMiddleware($locale='en'): LocaleMiddleware
    {
        return new LocaleMiddleware($this->getTranslatableSetter(), $locale);
    }

    public function testWithMessage()
    {
        $message = $this->createMock(MessageInterface::class);

        $manager = $this->createMock(ManagerInterface::class);

        $bag = $this->createMock(ParametersBag::class);


        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($message, $manager, $bag)
        );

        self::assertEquals(
            'en',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoInRequestNoInSession()
    {
        $bag = $this->createMock(ParametersBag::class);

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $manager = $this->createMock(ManagerInterface::class);

        $serverRequest->expects($this->once())
            ->method('withAttribute')
            ->with('locale', 'en')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        $serverRequest->expects($this->any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects($this->any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        self::assertEquals(
            'en',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoInRequestInSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);
        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);

        $serverRequest->expects($this->once())
            ->method('withAttribute')
            ->with('locale', 'fr')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->success('fr');

                return $sessionMiddleware;
            });

        $serverRequest->expects($this->any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects($this->any())
            ->method('getAttribute')
            ->willReturn(['foo' => 'bar']);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        self::assertEquals(
            'fr',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteInRequestInSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $bag = $this->createMock(ParametersBag::class);

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

        $serverRequest->expects($this->any())
            ->method('getQueryParams')
            ->willReturn(['locale' => 'es']);

        $serverRequest->expects($this->any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects($this->any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );

        self::assertEquals(
            'es',
            $this->getTranslatableSetter()->lang
        );
    }

    public function testExecuteNoSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);

        $serverRequest->expects($this->any())
            ->method('getQueryParams')
            ->willReturn([]);

        $serverRequestFinal->expects($this->any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }
}

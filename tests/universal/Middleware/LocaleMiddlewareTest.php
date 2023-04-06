<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Middleware;

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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\Common\Middleware\LocaleMiddleware
 */
class LocaleMiddlewareTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $translatableSetter;

    /**
     * @return callable|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTranslatableSetter(): callable
    {
        if (!$this->translatableSetter) {
            $this->translatableSetter = $this->getMockBuilder(\stdClass::class)
                ->addMethods(['__invoke'])
                ->getMock();
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

        $this->getTranslatableSetter()
            ->expects(self::once())
            ->method('__invoke')
            ->with('en');

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects(self::any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($message, $manager, $bag)
        );
    }

    public function testExecuteNoInRequestNoInSession()
    {
        $bag = $this->createMock(ParametersBag::class);

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $manager = $this->createMock(ManagerInterface::class);

        $this->getTranslatableSetter()
            ->expects(self::once())
            ->method('__invoke')
            ->with('en');

        $serverRequest->expects(self::once())
            ->method('withAttribute')
            ->with('locale', 'en')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects(self::any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->fail(new \DomainException());

                return $sessionMiddleware;
            });

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects(self::any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }

    public function testExecuteNoInRequestInSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);
        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);


        $this->getTranslatableSetter()
            ->expects(self::once())
            ->method('__invoke')
            ->with('fr');

        $serverRequest->expects(self::once())
            ->method('withAttribute')
            ->with('locale', 'fr')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects(self::any())
            ->method('get')
            ->willReturnCallback(function ($key, PromiseInterface $promise) use ($sessionMiddleware) {
                $promise->success('fr');

                return $sessionMiddleware;
            });

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects(self::any())
            ->method('getAttribute')
            ->willReturn(['foo' => 'bar']);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }

    public function testExecuteInRequestInSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())
            ->method('updateMessage')
            ->with($serverRequestFinal)
            ->willReturnSelf();

        $this->getTranslatableSetter()
            ->expects(self::once())
            ->method('__invoke')
            ->with('es');

        $serverRequest->expects(self::once())
            ->method('withAttribute')
            ->with('locale', 'es')
            ->willReturn($serverRequestFinal);

        $sessionMiddleware = $this->createMock(SessionInterface::class);
        $sessionMiddleware->expects(self::never())->method('get');
        $sessionMiddleware->expects(self::once())->method('set')->with('locale', 'es');

        $serverRequest->expects(self::any())
            ->method('getQueryParams')
            ->willReturn(['locale' => 'es']);

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->with(SessionInterface::ATTRIBUTE_KEY)
            ->willReturn($sessionMiddleware);

        $serverRequestFinal->expects(self::any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }

    public function testExecuteNoSession()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequestFinal = $this->createMock(ServerRequestInterface::class);

        $bag = $this->createMock(ParametersBag::class);

        $manager = $this->createMock(ManagerInterface::class);

        $serverRequest->expects(self::any())
            ->method('getQueryParams')
            ->willReturn([]);

        $serverRequestFinal->expects(self::any())
            ->method('getAttribute')
            ->willReturn([]);

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware('en')->execute($serverRequest, $manager, $bag)
        );
    }
}

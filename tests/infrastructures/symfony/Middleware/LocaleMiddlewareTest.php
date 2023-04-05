<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\CommonBundle\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Middleware\LocaleMiddleware
 */
class LocaleMiddlewareTest extends TestCase
{
    /**
     * @var LocaleAwareInterface
     */
    private $translator;

    /**
     * @return LocaleAwareInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTranslator(): LocaleAwareInterface
    {
        if (!$this->translator instanceof LocaleAwareInterface) {
            $this->translator = $this->createMock(LocaleAwareInterface::class);
        }

        return $this->translator;
    }

    /**
     * @return LocaleMiddleware
     */
    public function buildMiddleware()
    {
        return new LocaleMiddleware($this->getTranslator());
    }

    public function testUpdateLocaleInTranslatorIfLocalePresent()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->with('locale')
            ->willReturn('fr');

        $this->getTranslator()
            ->expects(self::once())
            ->method('setLocale')
            ->with('fr');

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $serverRequest,
            )
        );
    }

    public function testNotUpdateLocaleInTranslatorIfLocaleNotPresent()
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);

        $serverRequest->expects(self::any())
            ->method('getAttribute')
            ->with('locale')
            ->willReturn(null);

        $this->getTranslator()
            ->expects(self::never())
            ->method('setLocale');

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $serverRequest,
            )
        );
    }

    public function testWithMessage()
    {
        $message = $this->createMock(MessageInterface::class);

        $this->getTranslator()
            ->expects(self::never())
            ->method('setLocale');

        self::assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $message,
            )
        );
    }
}

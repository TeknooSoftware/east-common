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

namespace Teknoo\Tests\East\CommonBundle\Middleware;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Teknoo\East\CommonBundle\Middleware\LocaleMiddleware;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(LocaleMiddleware::class)]
class LocaleMiddlewareTest extends TestCase
{
    private (LocaleAwareInterface&MockObject)|null $translator = null;

    public function getTranslator(): LocaleAwareInterface&MockObject
    {
        if (!$this->translator instanceof LocaleAwareInterface) {
            $this->translator = $this->createMock(LocaleAwareInterface::class);
        }

        return $this->translator;
    }

    public function buildMiddleware(): LocaleMiddleware
    {
        return new LocaleMiddleware($this->getTranslator());
    }

    public function testUpdateLocaleInTranslatorIfLocalePresent(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);

        $serverRequest
            ->method('getAttribute')
            ->with('locale')
            ->willReturn('fr');

        $this->getTranslator()
            ->expects($this->once())
            ->method('setLocale')
            ->with('fr');

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $serverRequest,
            )
        );
    }

    public function testNotUpdateLocaleInTranslatorIfLocaleNotPresent(): void
    {
        $serverRequest = $this->createMock(ServerRequestInterface::class);

        $serverRequest
            ->method('getAttribute')
            ->with('locale')
            ->willReturn(null);

        $this->getTranslator()
            ->expects($this->never())
            ->method('setLocale');

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $serverRequest,
            )
        );
    }

    public function testWithMessage(): void
    {
        $message = $this->createMock(MessageInterface::class);

        $this->getTranslator()
            ->expects($this->never())
            ->method('setLocale');

        $this->assertInstanceOf(
            LocaleMiddleware::class,
            $this->buildMiddleware()->execute(
                $message,
            )
        );
    }
}

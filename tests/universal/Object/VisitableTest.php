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

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\Exception\BadMethodCallException;
use Teknoo\East\Common\Object\VisitableTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class VisitableTest extends TestCase
{
    public function buildObject(): \Teknoo\East\Common\Contracts\Object\VisitableInterface
    {
        return new class () implements VisitableInterface {
            use VisitableTrait;

            private string $foo = 'bar';

            private string $bar = 'foo';
        };
    }

    public function testExceptionWithArrayAndCallable(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->buildObject()->visit(['foo' => fn (): true => true], fn (): true => true);
    }

    public function testExceptionWithStringWithoutCallable(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->buildObject()->visit('foo');
    }

    public function testVisitWithArray(): void
    {
        $called = new class () {
            public int $counter = 0;
        };

        $this->buildObject()->visit(
            [
                'foo' => fn ($value): false => (++$called->counter) && $this->assertEquals('bar', $value),
                'bar' => fn ($value): false => (++$called->counter) && $this->assertEquals('foo', $value),
            ]
        );

        $this->assertEquals(2, $called->counter);
    }

    public function testVisitWithString(): void
    {
        $called = new class () {
            public int $counter = 0;
        };

        $this->buildObject()->visit(
            'foo',
            fn ($value): false => (++$called->counter) && $this->assertEquals('bar', $value),
        );

        $this->assertEquals(1, $called->counter);
    }
}

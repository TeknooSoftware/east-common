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

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Teknoo\East\Common\Object\Exception\BadMethodCallException;
use Teknoo\East\Common\Object\VisitableTrait;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class VisitableTest extends TestCase
{
    public function buildObject()
    {
        return new class implements VisitableInterface {
            use VisitableTrait;

            private $foo = 'bar';

            private $bar = 'foo';
        };
    }

    public function testExceptionWithArrayAndCallable()
    {
        $this->expectException(BadMethodCallException::class);
        $this->buildObject()->visit(['foo' => fn () => true], fn () => true);
    }

    public function testExceptionWithStringWithoutCallable()
    {
        $this->expectException(BadMethodCallException::class);
        $this->buildObject()->visit('foo');
    }

    public function testVisitWithArray()
    {
        $called = new class() {
            public int $counter = 0;
        };

        $this->buildObject()->visit(
            [
                'foo' => fn ($value) => (++$called->counter) && self::assertEquals('bar', $value),
                'bar' => fn ($value) => (++$called->counter) && self::assertEquals('foo', $value),
            ]
        );

        self::assertEquals(2, $called->counter);
    }

    public function testVisitWithString()
    {
        $called = new class() {
            public int $counter = 0;
        };

        $this->buildObject()->visit(
            'foo',
            fn ($value) => (++$called->counter) && self::assertEquals('bar', $value),
        );

        self::assertEquals(1, $called->counter);
    }
}
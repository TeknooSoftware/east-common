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

namespace Teknoo\Tests\East\Common\View;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Recipe\Ingredient\MergeableInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ParametersBag::class)]
class ParametersBagTest extends TestCase
{
    public function testSet(): void
    {
        $this->assertInstanceOf(
            ParametersBag::class,
            new ParametersBag()->set('foo', 'bar')
        );
    }

    public function testMerge(): void
    {
        $this->assertInstanceOf(
            ParametersBag::class,
            new ParametersBag()->merge(new ParametersBag())
        );

        $this->assertInstanceOf(
            ParametersBag::class,
            new ParametersBag()->merge($this->createStub(MergeableInterface::class))
        );
    }

    public function testTransform(): void
    {
        $this->assertEquals(
            ['foo' => 'bar', 'bar' => 'foo', 'hello' => 'world'],
            new ParametersBag()
                ->set('foo', 'bar2')
                ->set('bar', 'foo')
                ->merge(
                    new ParametersBag()
                        ->set('foo', 'bar')
                        ->set('hello', 'world')
                )
                ->transform()
        );
    }
}

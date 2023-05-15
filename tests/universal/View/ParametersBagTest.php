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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\View;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\View\ParametersBag;
use Teknoo\Recipe\Ingredient\MergeableInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\View\ParametersBag
 */
class ParametersBagTest extends TestCase
{
    public function testSet()
    {
        self::assertInstanceOf(
            ParametersBag::class,
            (new ParametersBag())->set('foo', 'bar')
        );
    }

    public function testMerge()
    {
        self::assertInstanceOf(
            ParametersBag::class,
            (new ParametersBag())->merge(new ParametersBag())
        );

        self::assertInstanceOf(
            ParametersBag::class,
            (new ParametersBag())->merge($this->createMock(MergeableInterface::class))
        );
    }

    public function testTransform()
    {
        self::assertEquals(
            ['foo' => 'bar', 'bar' => 'foo', 'hello' => 'world'],
            (new ParametersBag())
                ->set('foo', 'bar2')
                ->set('bar', 'foo')
                ->merge(
                    (new ParametersBag())
                        ->set('foo', 'bar')
                        ->set('hello', 'world')
                )
                ->transform()
        );
    }
}

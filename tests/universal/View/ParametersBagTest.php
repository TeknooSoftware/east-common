<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\View;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\View\ParametersBag;
use Teknoo\Recipe\Ingredient\MergeableInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\View\ParametersBag
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

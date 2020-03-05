<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Doctrine\Object;

use Teknoo\East\Website\Doctrine\Object\Item;
use Teknoo\Tests\East\Website\Object\ItemTest as OriginaTest;
/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Object\Item
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Item
 */
class ItemTest extends OriginaTest
{
    public function buildObject(): Item
    {
        return new Item();
    }

    public function testGetTranslatableLocale()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['localeField' => 'fooBar'])->getTranslatableLocale()
        );
    }

    public function testSetTranslatableLocale()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setTranslatableLocale('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getLocaleField()
        );
    }

}
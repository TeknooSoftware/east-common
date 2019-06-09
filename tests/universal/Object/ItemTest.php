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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Item;
use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Item
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    use ObjectTestTrait;

    /**
     * @return Item
     */
    public function buildObject(): Item
    {
        return new Item();
    }

    public function testGetName()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['name' => 'fooBar'])->getName()
        );
    }

    public function testToString()
    {
        self::assertEquals(
            'fooBar',
            (string) $this->generateObjectPopulated(['name' => 'fooBar'])
        );
    }

    public function testSetName()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setName('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getName()
        );
    }

    public function testSetNameExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setName(new \stdClass());
    }

    public function testGetSlug()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['slug' => 'fooBar'])->getSlug()
        );
    }

    public function testSetSlug()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setSlug('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getSlug()
        );
    }

    public function testSetSlugExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setSlug(new \stdClass());
    }

    public function testGetContent()
    {
        $object = new Content();
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['content' => $object])->getContent()
        );
    }

    public function testSetContent()
    {
        $object = new Content();

        $Object = $this->buildObject();
        self::assertInstanceOf(
            Item::class,
            $Object->setContent($object)
        );

        self::assertEquals(
            $object,
            $Object->getContent()
        );

        self::assertInstanceOf(
            Item::class,
            $Object->setContent(null)
        );

        self::assertEmpty($Object->getContent());
    }

    public function testSetContentExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setContent(new \stdClass());
    }

    public function testGetLocation()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['location' => 'fooBar'])->getLocation()
        );
    }

    public function testSetLocation()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setLocation('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getLocation()
        );
    }

    public function testSetLocationExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLocation(new \stdClass());
    }

    public function testIsHidden()
    {
        self::assertFalse($this->generateObjectPopulated(['hidden' => false])->isHidden());
        self::assertTrue($this->generateObjectPopulated(['hidden' => true])->isHidden());
    }

    public function testSetHidden()
    {
        $Object = $this->buildObject();
        self::assertFalse($Object->isHidden());
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setHidden(true)
        );

        self::assertTrue($Object->isHidden());
    }

    public function testGetLocaleField()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['localeField' => 'fooBar'])->getLocaleField()
        );
    }

    public function testGetTranslatableLocale()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['localeField' => 'fooBar'])->getTranslatableLocale()
        );
    }

    public function testSetLocaleField()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setLocaleField('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getLocaleField()
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

    public function testSetLocaleFieldExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLocaleField(new \stdClass());
    }

    public function testGetPosition()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['position' => 123])->getPosition()
        );
    }

    public function testSetPosition()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setPosition(123)
        );

        self::assertEquals(
            123,
            $Object->getPosition()
        );
    }

    public function testSetPositionExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setPosition(new \stdClass());
    }
    
    public function testGetParent()
    {
        $object = new Item();
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['parent' => $object])->getParent()
        );
    }

    public function testSetParent()
    {
        $object = new Item();

        $Object = $this->buildObject();
        self::assertInstanceOf(
            Item::class,
            $Object->setParent($object)
        );

        self::assertEquals(
            $object,
            $Object->getParent()
        );

        self::assertInstanceOf(
            Item::class,
            $Object->setParent(null)
        );

        self::assertEmpty($Object->getParent());
    }

    public function testSetParentExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setParent(new \stdClass());
    }

    public function testGetChildren()
    {
        self::assertEquals(
            [],
            $this->generateObjectPopulated(['children' => []])->getChildren()
        );
    }

    public function testSetChildren()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setChildren(['foo'=>'bar'])
        );

        self::assertEquals(
            ['foo'=>'bar'],
            $Object->getChildren()
        );
    }

    public function testSetChildrenExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setChildren(new \stdClass());
    }

    public function testStatesListDeclaration()
    {
        self::assertIsArray(Item::statesListDeclaration());
    }
}

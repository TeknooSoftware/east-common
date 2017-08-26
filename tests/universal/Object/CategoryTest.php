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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use Teknoo\East\Website\Object\Category;
use Teknoo\East\Website\Object\Type;
use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;

/**
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Category
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    use ObjectTestTrait;

    /**
     * @return Category
     */
    public function buildObject(): Category
    {
        return new Category();
    }

    public function testGetName()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['name' => 'fooBar'])->getName()
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

    /**
     * @expectedException \Throwable
     */
    public function testSetNameExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetSlugExceptionOnBadArgument()
    {
        $this->buildObject()->setSlug(new \stdClass());
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

    /**
     * @expectedException \Throwable
     */
    public function testSetLocationExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetLocaleFieldExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetPositionExceptionOnBadArgument()
    {
        $this->buildObject()->setPosition(new \stdClass());
    }
    
    public function testGetParent()
    {
        $object = new Category();
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['parent' => $object])->getParent()
        );
    }

    public function testSetParent()
    {
        $object = new Category();

        $Object = $this->buildObject();
        self::assertInstanceOf(
            Category::class,
            $Object->setParent($object)
        );

        self::assertEquals(
            $object,
            $Object->getParent()
        );

        self::assertInstanceOf(
            Category::class,
            $Object->setParent(null)
        );

        self::assertEmpty($Object->getParent());
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetParentExceptionOnBadArgument()
    {
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

    /**
     * @expectedException \Throwable
     */
    public function testSetChildrenExceptionOnBadArgument()
    {
        $this->buildObject()->setChildren(new \stdClass());
    }

    public function testStatesListDeclaration()
    {
        self::assertInternalType('array', Category::statesListDeclaration());
    }
}
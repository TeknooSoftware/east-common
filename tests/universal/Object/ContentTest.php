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

use Teknoo\Tests\East\Website\Object\Traits\PublishableTestTrait;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Content
 * @covers \Teknoo\East\Website\Object\Content\Draft
 */
class ContentTest extends \PHPUnit\Framework\TestCase
{
    use PublishableTestTrait;

    /**
     * @return Content
     */
    public function buildObject(): Content
    {
        return new Content();
    }

    public function testGetParts()
    {
        self::assertEquals(
            ['fooBar'],
            $this->generateObjectPopulated(['parts' => \json_encode(['fooBar'])])->getParts()
        );
    }

    public function testSetParts()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setParts(['fooBar'])
        );

        self::assertEquals(
            ['fooBar'],
            $Object->getParts()
        );
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setParts(null)
        );

        self::assertEmpty(
            $Object->getParts()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetPartsExceptionOnBadArgument()
    {
        $this->buildObject()->setContent(new \stdClass());
    }

    public function testGetDescription()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['description' => 'fooBar'])->getDescription()
        );
    }

    public function testSetDescription()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setDescription('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getDescription()
        );

        self::assertInstanceOf(
            \get_class($Object),
            $Object->setDescription(null)
        );

        self::assertEmpty(
            $Object->getDescription()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetDescriptionExceptionOnBadArgument()
    {
        $this->buildObject()->setDescription(new \stdClass());
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

    public function testGetSubtitle()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['subtitle' => 'fooBar'])->getSubtitle()
        );
    }

    public function testSetSubtitle()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setSubtitle('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getSubtitle()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetSubtitleExceptionOnBadArgument()
    {
        $this->buildObject()->setSubtitle(new \stdClass());
    }

    public function testGetTitle()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['title' => 'fooBar'])->getTitle()
        );
    }

    public function testToString()
    {
        self::assertEquals(
            'fooBar',
            (string) $this->generateObjectPopulated(['title' => 'fooBar'])
        );
    }

    public function testSetTitle()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setTitle('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getTitle()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetTitleExceptionOnBadArgument()
    {
        $this->buildObject()->setTitle(new \stdClass());
    }

    public function testGetAuthor()
    {
        $object = new User();
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['author' => $object])->getAuthor()
        );
    }

    public function testSetAuthor()
    {
        $object = new User();

        $Object = $this->buildObject();
        self::assertInstanceOf(
            Content::class,
            $Object->setAuthor($object)
        );

        self::assertEquals(
            $object,
            $Object->getAuthor()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetAuthorExceptionOnBadArgument()
    {
        $this->buildObject()->setAuthor(new \stdClass());
    }

    public function testGetType()
    {
        $object = new Type();
        self::assertEquals(
            $object,
            $this->generateObjectPopulated(['type' => $object])->getType()
        );
    }

    public function testSetType()
    {
        $object = new Type();

        $Object = $this->buildObject();
        self::assertInstanceOf(
            Content::class,
            $Object->setType($object)
        );

        self::assertEquals(
            $object,
            $Object->getType()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetTypeExceptionOnBadArgument()
    {
        $this->buildObject()->setType(new \stdClass());
    }
    
    public function testGetCategories()
    {
        self::assertEquals(
            [],
            $this->generateObjectPopulated(['categories' => []])->getCategories()
        );
    }

    public function testSetCategories()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setCategories(['foo'=>'bar'])
        );

        self::assertEquals(
            ['foo'=>'bar'],
            $Object->getCategories()
        );
    }

    public function testGetTags()
    {
        self::assertEquals(
            [],
            $this->generateObjectPopulated(['tags' => []])->getTags()
        );
    }

    public function testSetTags()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setTags(['foo'=>'bar'])
        );

        self::assertEquals(
            ['foo'=>'bar'],
            $Object->getTags()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetTagsExceptionOnBadArgument()
    {
        $this->buildObject()->setTags(new \stdClass());
    }

    public function testStatesListDeclaration()
    {
        self::assertInternalType('array', Content::statesListDeclaration());
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

    /**
     * @expectedException \Throwable
     */
    public function testSetLocaleFieldExceptionOnBadArgument()
    {
        $this->buildObject()->setLocaleField(new \stdClass());
    }
}

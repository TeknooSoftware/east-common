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

namespace Teknoo\Tests\East\Website\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Service\FindSlugService;
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
 * @covers \Teknoo\East\Website\Object\Content\Published
 */
class ContentTest extends TestCase
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

    public function testSetPartsExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
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

    public function testSetDescriptionExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setDescription(new \stdClass());
    }

    public function testGetSlug()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['slug' => 'fooBar'])->getSlug()
        );
    }

    public function testPrepareSlugNear()
    {
        $loader = $this->createMock(LoaderInterface::class);

        $findSlugService = $this->createMock(FindSlugService::class);
        $findSlugService->expects(self::once())->method('process');

        self::assertInstanceOf(
            Content::class,
            $this->buildObject()->prepareSlugNear(
                $loader,
                $findSlugService,
                'slug'
            )
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

        self::assertInstanceOf(
            \get_class($Object),
            $Object->setSlug('helloWorld')
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

    public function testSetSubtitleExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
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

    public function testSetTitleExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
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

    public function testSetAuthorExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
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

    public function testSetTypeExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setType(new \stdClass());
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

    public function testSetTagsExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setTags(new \stdClass());
    }

    public function testStatesListDeclaration()
    {
        self::assertIsArray(Content::statesListDeclaration());
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

    public function testSetLocaleFieldExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLocaleField(new \stdClass());
    }
}

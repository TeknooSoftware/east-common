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

use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;
use Teknoo\East\Website\Object\Media;
use Teknoo\Tests\East\Website\Object\Traits\PublishableTestTrait;

/**
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\Media
 */
class MediaTest extends \PHPUnit_Framework_TestCase
{
    use PublishableTestTrait;

    /**
     * @return Media
     */
    public function buildObject(): Media
    {
        return new Media();
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
    
    public function testGetSize()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['size' => 123])->getSize()
        );
    }

    public function testSetSize()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setSize(123)
        );

        self::assertEquals(
            123,
            $Object->getSize()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetSizeExceptionOnBadArgument()
    {
        $this->buildObject()->setSize(new \stdClass());
    }

    public function testGetMimeType()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['mimeType' => 'fooBar'])->getMimeType()
        );
    }

    public function testSetMimeType()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setMimeType('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getMimeType()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetMimeTypeExceptionOnBadArgument()
    {
        $this->buildObject()->setMimeType(new \stdClass());
    }

    public function testGetAlternative()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['alternative' => 'fooBar'])->getAlternative()
        );
    }

    public function testSetAlternative()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setAlternative('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getAlternative()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetAlternativeExceptionOnBadArgument()
    {
        $this->buildObject()->setAlternative(new \stdClass());
    }

    public function testGetRaw()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['raw' => 'fooBar'])->getRaw()
        );
    }

    public function testSetRaw()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setRaw('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getRaw()
        );
    }
}
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

namespace Teknoo\Tests\East\Website\Object\Traits;

trait ObjectTestTrait
{
    use PopulateObjectTrait;

    public function testGetId()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['id' => 123])->getId()
        );
    }

    public function testSetId()
    {
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setId('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $Object->getId()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetIdExceptionOnBadArgument()
    {
        $this->buildObject()->setId(new \stdClass());
    }

    public function testCreatedAt()
    {
        $date = new \DateTime('2017-06-13');
        self::assertEquals(
            $date,
            $this->generateObjectPopulated(['createdAt' => $date])->createdAt()
        );
    }

    public function testGetUpdatedAt()
    {
        $date = new \DateTime('2017-06-13');
        self::assertEquals(
            $date,
            $this->generateObjectPopulated(['updatedAt' => $date])->updatedAt()
        );
    }

    public function testGetDeletedAt()
    {
        $date = new \DateTime('2017-06-13');
        self::assertEquals(
            $date,
            $this->generateObjectPopulated(['deletedAt' => $date])->getDeletedAt()
        );
    }

    public function testSetDeletedAt()
    {
        $date = new \DateTime('2017-06-13');
        
        $Object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($Object),
            $Object->setDeletedAt($date)
        );

        self::assertEquals(
            $date,
            $Object->getDeletedAt()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetDeletedAtExceptionOnBadArgument()
    {
        $this->buildObject()->setDeletedAt(new \stdClass());
    }
}
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

namespace Teknoo\Tests\East\Common\Object\Traits;

use Teknoo\East\Common\Contracts\Object\DeletableInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ObjectTestTrait
{
    use PopulateObjectTrait;

    public function testGetId(): void
    {
        $this->assertEquals(
            123,
            $this->generateObjectPopulated(['id' => 123])->getId()
        );
    }

    public function testSetId(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setId('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getId()
        );
    }

    public function testSetIdExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setId(new \stdClass());
    }

    public function testCreatedAt(): void
    {
        $date = new \DateTime('2017-06-13');
        $this->assertEquals(
            $date,
            $this->generateObjectPopulated(['createdAt' => $date])->createdAt()
        );
    }

    public function testUpdatedAt(): void
    {
        $date = new \DateTime('2017-06-13');
        $this->assertEquals(
            $date,
            $this->generateObjectPopulated(['updatedAt' => $date])->updatedAt()
        );
    }

    public function testSetUpdatedAt(): void
    {
        $date = new \DateTime('2017-06-13');

        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setUpdatedAt($date)
        );

        $this->assertEquals(
            $date,
            $object->updatedAt()
        );
    }

    public function testSetUpdatedAtExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setUpdatedAt(new \stdClass());
    }

    public function testDeletedAt(): void
    {
        $object = $this->buildObject();
        if (!$object instanceof DeletableInterface) {
            $this->assertTrue(true); //To avoid warning about skipped test
            return;
        }

        $date = new \DateTime('2017-06-13');
        $this->assertEquals(
            $date,
            $this->generateObjectPopulated(['deletedAt' => $date])->getDeletedAt()
        );
    }

    public function testSetDeletedAt(): void
    {
        $object = $this->buildObject();
        if (!$object instanceof DeletableInterface) {
            $this->assertTrue(true); //To avoid warning about skipped test
            return;
        }

        $date = new \DateTime('2017-06-13');

        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setDeletedAt($date)
        );

        $this->assertEquals(
            $date,
            $object->getDeletedAt()
        );
    }

    public function testSetDeletedAtExceptionOnBadArgument(): void
    {
        $object = $this->buildObject();
        if (!$object instanceof DeletableInterface) {
            $this->assertTrue(true); //To avoid warning about skipped test
            return;
        }

        $this->expectException(\Throwable::class);
        $this->buildObject()->setDeletedAt(new \stdClass());
    }
}

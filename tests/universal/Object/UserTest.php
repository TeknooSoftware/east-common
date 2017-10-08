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
use Teknoo\East\Website\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\PublishableTrait
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\User
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    use ObjectTestTrait;

    /**
     * @return User
     */
    public function buildObject(): User
    {
        return new User();
    }

    public function testGetFirstName()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['firstName' => 'fooBar'])->getFirstName()
        );
    }

    public function testSetFirstName()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setFirstName('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getFirstName()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetFirstNameExceptionOnBadArgument()
    {
        $this->buildObject()->setFirstName(new \stdClass());
    }

    public function testGetLastName()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['lastName' => 'fooBar'])->getLastName()
        );
    }

    public function testSetLastName()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setLastName('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getLastName()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetLastNameExceptionOnBadArgument()
    {
        $this->buildObject()->setLastName(new \stdClass());
    }

    public function testGetEmail()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getEmail()
        );
    }

    public function testGetUsername()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getUsername()
        );
    }

    public function testSetEmail()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setEmail('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getEmail()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetEmailExceptionOnBadArgument()
    {
        $this->buildObject()->setEmail(new \stdClass());
    }

    public function testGetPassword()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['password' => 'fooBar'])->getPassword()
        );
    }

    public function testSetPassword()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getPassword()
        );
    }

    public function testEraseCredentials()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getPassword()
        );

        self::assertEquals(
            'fooBar',
            $object->getOriginalPassword()
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar2')
        );

        self::assertEquals(
            'fooBar2',
            $object->getPassword()
        );

        self::assertEquals(
            'fooBar',
            $object->getOriginalPassword()
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->eraseCredentials()
        );

        self::assertEmpty($object->getPassword());
        self::assertEmpty($object->getOriginalPassword());
    }
    
    public function testHasUpdatedPassword()
    {
        $object = $this->buildObject();
        self::assertFalse($object->hasUpdatedPassword());
        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertTrue($object->hasUpdatedPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar2')
        );

        self::assertTrue($object->hasUpdatedPassword());

        $object = $this->buildObject(['password' => 'fooBar']);
        self::assertFalse($object->hasUpdatedPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword(null)
        );

        self::assertFalse($object->hasUpdatedPassword());

        $object = $this->buildObject();
        $refProperty = new \ReflectionProperty($object, 'password');
        $refProperty->setAccessible(true);
        $refProperty->setValue($object, 'fooBar');

        self::assertTrue($object->hasUpdatedPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar')
        );

        self::assertFalse($object->hasUpdatedPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar2')
        );

        self::assertTrue($object->hasUpdatedPassword());

        self::assertInstanceOf(
            \get_class($object),
            $object->setPassword('fooBar3')
        );

        self::assertTrue($object->hasUpdatedPassword());
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetPasswordExceptionOnBadArgument()
    {
        $this->buildObject()->setPassword(new \stdClass());
    }

    public function testGetSalt()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['salt' => 'fooBar'])->getSalt()
        );
    }

    public function testSetSalt()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setSalt('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getSalt()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetSaltExceptionOnBadArgument()
    {
        $this->buildObject()->setSalt(new \stdClass());
    }
    
    public function testGetRoles()
    {
        self::assertEquals(
            [],
            $this->generateObjectPopulated(['roles' => []])->getRoles()
        );
    }

    public function testSetRoles()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setRoles(['foo'=>'bar'])
        );

        self::assertEquals(
            ['foo'=>'bar'],
            $object->getRoles()
        );
    }

    /**
     * @expectedException \Throwable
     */
    public function testSetRolesExceptionOnBadArgument()
    {
        $this->buildObject()->setRoles(new \stdClass());
    }
}
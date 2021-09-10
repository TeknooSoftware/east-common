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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Contracts\User\AuthDataInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\Tests\East\Website\Object\Traits\ObjectTestTrait;
use Teknoo\East\Website\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Object\ObjectTrait
 * @covers \Teknoo\East\Website\Object\User
 */
class UserTest extends TestCase
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

    public function testToString()
    {
        self::assertEquals(
            'foo Bar',
            (string) $this->generateObjectPopulated(['firstName' => 'foo', 'lastName' => 'Bar'])
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

    public function testSetFirstNameExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
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

    public function testSetLastNameExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLastName(new \stdClass());
    }

    public function testGetEmail()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getEmail()
        );
    }

    public function testGetUserIdentifier()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getUserIdentifier()
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

    public function testSetEmailExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setEmail(new \stdClass());
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

    public function testSetRolesExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setRoles(new \stdClass());
    }

    public function testGetAuthData()
    {
        self::assertEquals(
            [],
            $this->generateObjectPopulated(['authData' => []])->getAuthData()
        );
    }

    public function testSetAuthData()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->setAuthData([$this->createMock(AuthDataInterface::class)])
        );

        self::assertEquals(
            [$this->createMock(AuthDataInterface::class)],
            $object->getAuthData()
        );
    }

    public function testAddAuthData()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            \get_class($object),
            $object->addAuthData(
                $ad1 = $this->createMock(AuthDataInterface::class)
            )
        );

        self::assertEquals(
            [$ad1],
            $object->getAuthData()
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->addAuthData(
                $ad2 = $this->createMock(AuthDataInterface::class)
            )
        );

        self::assertEquals(
            [$ad1, $ad2],
            $object->getAuthData()
        );
    }

    public function testAddAuthDataWithIterator()
    {
        $object = $this->generateObjectPopulated(
            [
                'authData' => new \ArrayIterator([
                    $ad1 = $this->createMock(AuthDataInterface::class)
                ])
            ]
        );

        self::assertInstanceOf(
            \get_class($object),
            $object->addAuthData(
                $ad2 = $this->createMock(AuthDataInterface::class)
            )
        );

        self::assertEquals(
            [$ad1, $ad2],
            $object->getAuthData()
        );
    }

    public function testSetAuthDataExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setAuthData(new \stdClass());
    }
}

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

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\Tests\East\Common\Object\Traits\ObjectTestTrait;
use Teknoo\East\Common\Object\User;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(User::class)]
class UserTest extends TestCase
{
    use ObjectTestTrait;

    public function buildObject(): User
    {
        return new User();
    }

    public function testGetFirstName(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['firstName' => 'fooBar'])->getFirstName()
        );
    }

    public function testToString(): void
    {
        $this->assertEquals(
            'foo Bar',
            (string) $this->generateObjectPopulated(['firstName' => 'foo', 'lastName' => 'Bar'])
        );
    }

    public function testSetFirstName(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setFirstName('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getFirstName()
        );
    }

    public function testSetFirstNameExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setFirstName(new \stdClass());
    }

    public function testGetLastName(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['lastName' => 'fooBar'])->getLastName()
        );
    }

    public function testSetLastName(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setLastName('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getLastName()
        );
    }

    public function testSetLastNameExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLastName(new \stdClass());
    }

    public function testGetEmail(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getEmail()
        );
    }

    public function testGetUserIdentifier(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['email' => 'fooBar'])->getUserIdentifier()
        );
    }

    public function testGetUserIdentifierExceptionOnEmpty(): void
    {
        $this->expectException(\DomainException::class);
        $this->generateObjectPopulated(['email' => ''])->getUserIdentifier();
    }

    public function testSetEmail(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setEmail('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getEmail()
        );
    }

    public function testSetEmailExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setEmail(new \stdClass());
    }

    public function testGetRoles(): void
    {
        $this->assertEquals(
            [],
            $this->generateObjectPopulated(['roles' => []])->getRoles()
        );
    }

    public function testSetRoles(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setRoles(['foo' => 'bar'])
        );

        $this->assertEquals(
            ['foo' => 'bar'],
            $object->getRoles()
        );
    }

    public function testSetRolesExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setRoles(new \stdClass());
    }

    public function testGetAuthData(): void
    {
        $this->assertEquals(
            [],
            $this->generateObjectPopulated(['authData' => []])->getAuthData()
        );
    }

    public function testGetOneAuthData(): void
    {
        $this->assertNull(
            $this->generateObjectPopulated(['authData' => []])->getOneAuthData(
                StoredPassword::class,
            )
        );
        $this->assertNull(
            $this->generateObjectPopulated(['authData' => [
                new ThirdPartyAuth()
            ]])->getOneAuthData(
                StoredPassword::class,
            )
        );
        $this->assertInstanceOf(
            StoredPassword::class,
            $this->generateObjectPopulated(['authData' => [
                new StoredPassword()
            ]])->getOneAuthData(
                StoredPassword::class,
            )
        );
    }

    public function testSetAuthData(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setAuthData([$this->createMock(AuthDataInterface::class)])
        );

        $this->assertEquals(
            [$this->createMock(AuthDataInterface::class)],
            $object->getAuthData()
        );
    }

    public function testAddAuthData(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->addAuthData(
                $ad1 = $this->createMock(AuthDataInterface::class)
            )
        );

        $this->assertEquals(
            [$ad1],
            $object->getAuthData()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->addAuthData(
                $ad1b = $this->createMock(AuthDataInterface::class)
            )
        );

        $this->assertEquals(
            [$ad1b],
            $object->getAuthData()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->addAuthData(
                $ad2 = $this->createMock(StoredPassword::class)
            )
        );

        $this->assertEquals(
            [$ad1b, $ad2],
            $object->getAuthData()
        );
    }

    public function testAddAuthDataWithIterator(): void
    {
        $object = $this->generateObjectPopulated(
            [
                'authData' => new \ArrayIterator([
                    $ad1 = $this->createMock(AuthDataInterface::class)
                ])
            ]
        );

        $this->assertInstanceOf(
            $object::class,
            $object->addAuthData(
                $ad2 = $this->createMock(StoredPassword::class)
            )
        );

        $this->assertEquals(
            [$ad1, $ad2],
            $object->getAuthData()
        );
    }

    public function testSetAuthDataExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setAuthData(new \stdClass());
    }

    public function testRemoveAuthData(): void
    {
        $object = $this->generateObjectPopulated(
            [
                'authData' => new \ArrayIterator([
                    $a = $this->createMock(StoredPassword::class),
                    $this->createMock(ThirdPartyAuth::class),
                    $b = $this->createMock(TOTPAuth::class),
                ])
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $object->removeAuthData(ThirdPartyAuth::class),
        );

        $this->assertEquals(
            [
                $a,
                $b,
            ],
            $object->getAuthData(),
        );
    }

    public function testRemoveAuthDataButDataToRemoveMissing(): void
    {
        $object = $this->generateObjectPopulated(
            [
                'authData' => new \ArrayIterator([
                    $a = $this->createMock(StoredPassword::class),
                    $b = $this->createMock(TOTPAuth::class),
                ])
            ]
        );

        $this->assertInstanceOf(
            User::class,
            $object->removeAuthData(ThirdPartyAuth::class),
        );

        $this->assertEquals(
            [
                $a,
                $b,
            ],
            $object->getAuthData(),
        );
    }

    public function testRemoveAuthDataExceptionOnBadClass(): void
    {
        $this->expectException(\DomainException::class);
        $this->buildObject()->removeAuthData('fooooooooooo');
    }

    public function testRemoveAuthDataExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->removeAuthData(new \stdClass());
    }

    public function testGetActive(): void
    {
        $this->assertTrue(
            $this->generateObjectPopulated(['active' => true])->isActive()
        );
        $this->assertFalse(
            $this->generateObjectPopulated(['active' => false])->isActive()
        );
        $this->assertTrue(
            $this->generateObjectPopulated()->isActive()
        );
    }

    public function testSetActive(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setActive(true)
        );

        $this->assertTrue(
            $object->isActive()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->setActive(false)
        );

        $this->assertFalse(
            $object->isActive()
        );
    }

    public function testSetActiveExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setActive(new \stdClass());
    }

    public function testExportToMeDataBadNormalizer(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildObject()->exportToMeData(new \stdClass(), []);
    }

    public function testExportToMeDataBadContext(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildObject()->exportToMeData(
            $this->createMock(EastNormalizerInterface::class),
            new \stdClass()
        );
    }

    public function testExportToMe(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => User::class,
                'id' => '123',
                'firstName' => 'fooName',
                'email' => '',
                'lastName' => '',
            ]);

        $this->assertInstanceOf(
            User::class,
            $this->buildObject()->setId('123')->setFirstName('fooName')->exportToMeData(
                $normalizer,
                ['foo' => 'bar']
            )
        );
    }

    public function testSetExportConfiguration(): void
    {
        User::setExportConfiguration($conf = ['name' => ['default']]);
        $rc = new ReflectionClass(User::class);

        $this->assertEquals(
            $conf,
            $rc->getStaticPropertyValue('exportConfigurations'),
        );
    }
}

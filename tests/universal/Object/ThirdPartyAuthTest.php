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
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ThirdPartyAuth::class)]
class ThirdPartyAuthTest extends TestCase
{
    use PopulateObjectTrait;

    public function buildObject(): ThirdPartyAuth
    {
        return new ThirdPartyAuth();
    }

    public function testGetProtocol(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['protocol' => 'fooBar'])->getProtocol()
        );
    }

    public function testSetProtocol(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setProtocol('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getProtocol()
        );
    }

    public function testGetProvider(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['provider' => 'fooBar'])->getProvider()
        );
    }

    public function testSetProvider(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setProvider('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getProvider()
        );
    }

    public function testGetToken(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['token' => 'fooBar'])->getToken()
        );
    }

    public function testSetToken(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setToken('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getToken()
        );
    }

    public function testGetUserIdentifier(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['userIdentifier' => 'fooBar'])->getUserIdentifier()
        );
    }

    public function testSetUserIdentifier(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setUserIdentifier('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getUserIdentifier()
        );
    }
}

<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ThirdPartyAuth::class)]
class ThirdPartyAuthTest extends TestCase
{
    use PopulateObjectTrait;

    /**
     * @return ThirdPartyAuth
     */
    public function buildObject(): ThirdPartyAuth
    {
        return new ThirdPartyAuth();
    }

    public function testGetProtocol()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['protocol' => 'fooBar'])->getProtocol()
        );
    }

    public function testSetProtocol()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setProtocol('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getProtocol()
        );
    }

    public function testGetProvider()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['provider' => 'fooBar'])->getProvider()
        );
    }

    public function testSetProvider()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setProvider('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getProvider()
        );
    }

    public function testGetToken()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['token' => 'fooBar'])->getToken()
        );
    }

    public function testSetToken()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setToken('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getToken()
        );
    }

    public function testGetUserIdentifier()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['userIdentifier' => 'fooBar'])->getUserIdentifier()
        );
    }

    public function testSetUserIdentifier()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setUserIdentifier('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getUserIdentifier()
        );
    }
}

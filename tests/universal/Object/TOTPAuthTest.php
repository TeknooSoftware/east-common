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
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(TOTPAuth::class)]
class TOTPAuthTest extends TestCase
{
    use PopulateObjectTrait;

    public function buildObject(): TOTPAuth
    {
        return new TOTPAuth();
    }

    public function testGetTopSecret(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['topSecret' => 'fooBar'])->getTopSecret()
        );
    }

    public function testSetTopSecret(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setTopSecret('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getTopSecret()
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

    public function testGetAlgorithm(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['algorithm' => 'fooBar'])->getAlgorithm()
        );
    }

    public function testSetAlgorithm(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setAlgorithm('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getAlgorithm()
        );
    }

    public function testGetPeriod(): void
    {
        $this->assertEquals(
            123,
            $this->generateObjectPopulated(['period' => 123])->getPeriod()
        );
    }

    public function testSetPeriod(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setPeriod(123)
        );

        $this->assertEquals(
            123,
            $object->getPeriod()
        );
    }

    public function testGetDigits(): void
    {
        $this->assertEquals(
            123,
            $this->generateObjectPopulated(['digits' => 123])->getDigits()
        );
    }

    public function testSetDigits(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setDigits(123)
        );

        $this->assertEquals(
            123,
            $object->getDigits()
        );
    }

    public function testGetEnabled(): void
    {
        $this->assertTrue(
            $this->generateObjectPopulated(['enabled' => true])->isEnabled()
        );
    }

    public function testSetEnabled(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setEnabled(false)
        );

        $this->assertFalse(
            $object->isEnabled()
        );
    }
}

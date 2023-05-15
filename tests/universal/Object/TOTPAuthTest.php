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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\East\Common\Object\TOTPAuth
 */
class TOTPAuthTest extends TestCase
{
    use PopulateObjectTrait;

    /**
     * @return TOTPAuth
     */
    public function buildObject(): TOTPAuth
    {
        return new TOTPAuth();
    }

    public function testGetTopSecret()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['topSecret' => 'fooBar'])->getTopSecret()
        );
    }

    public function testSetTopSecret()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setTopSecret('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getTopSecret()
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

    public function testGetAlgorithm()
    {
        self::assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['algorithm' => 'fooBar'])->getAlgorithm()
        );
    }

    public function testSetAlgorithm()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setAlgorithm('fooBar')
        );

        self::assertEquals(
            'fooBar',
            $object->getAlgorithm()
        );
    }

    public function testGetPeriod()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['period' => 123])->getPeriod()
        );
    }

    public function testSetPeriod()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setPeriod(123)
        );

        self::assertEquals(
            123,
            $object->getPeriod()
        );
    }

    public function testGetDigits()
    {
        self::assertEquals(
            123,
            $this->generateObjectPopulated(['digits' => 123])->getDigits()
        );
    }

    public function testSetDigits()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setDigits(123)
        );

        self::assertEquals(
            123,
            $object->getDigits()
        );
    }

    public function testGetEnabled()
    {
        self::assertTrue(
            $this->generateObjectPopulated(['enabled' => true])->isEnabled()
        );
    }

    public function testSetEnabled()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setEnabled(false)
        );

        self::assertFalse(
            $object->isEnabled()
        );
    }
}

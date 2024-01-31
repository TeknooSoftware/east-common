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
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Object\RecoveryAccess
 */
class RecoveryAccessTest extends TestCase
{
    use PopulateObjectTrait;

    /**
     * @return RecoveryAccess
     */
    public function buildObject(): RecoveryAccess
    {
        return new RecoveryAccess();
    }

    public function testGetParams()
    {
        self::assertEquals(
            ['fooBar'],
            $this->generateObjectPopulated(['params' => ['fooBar']])->getParams()
        );
    }

    public function testSetParams()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setParams(['fooBar'])
        );

        self::assertEquals(
            ['fooBar'],
            $object->getParams()
        );

        self::assertInstanceOf(
            $object::class,
            $object->setParams([])
        );

        self::assertEmpty(
            $object->getParams()
        );
    }

    public function testEraseCredentials()
    {
        $object = $this->buildObject();
        self::assertInstanceOf(
            $object::class,
            $object->setParams(['fooBar'])
        );

        self::assertEquals(
            ['fooBar'],
            $object->getParams()
        );

        self::assertInstanceOf(
            $object::class,
            $object->setParams(['fooBar2'])
        );

        self::assertEquals(
            ['fooBar2'],
            $object->getParams()
        );

        self::assertInstanceOf(
            $object::class,
            $object->eraseCredentials()
        );

        self::assertEmpty($object->getParams());
    }

    public function testSetParamsExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setParams(new \stdClass());
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

    public function testSetAlgorithmExceptionOnBadArgument()
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setAlgorithm(new \stdClass());
    }
}

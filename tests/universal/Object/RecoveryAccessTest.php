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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RecoveryAccess::class)]
class RecoveryAccessTest extends TestCase
{
    use PopulateObjectTrait;

    public function buildObject(): RecoveryAccess
    {
        return new RecoveryAccess(TimeLimitedToken::class);
    }

    public function testWrongAlgorithm(): void
    {
        $this->expectException(DomainException::class);
        new RecoveryAccess(stdClass::class);
    }

    public function testGetParams(): void
    {
        $this->assertEquals(
            ['fooBar'],
            $this->generateObjectPopulated(['params' => ['fooBar']])->getParams()
        );
    }

    public function testSetParams(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setParams(['fooBar'])
        );

        $this->assertEquals(
            ['fooBar'],
            $object->getParams()
        );

        $this->assertInstanceOf(
            $object::class,
            $object->setParams([])
        );

        $this->assertEmpty(
            $object->getParams()
        );
    }

    public function testSetParamsExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setParams(new stdClass());
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

    public function testSetAlgorithmExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setAlgorithm(new stdClass());
    }
}

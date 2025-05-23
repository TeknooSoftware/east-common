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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Service\DeletingService;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteObject::class)]
class DeleteObjectTest extends TestCase
{
    public function buildStep(): DeleteObject
    {
        return new DeleteObject();
    }

    public function testInvokeBadDeletingService()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(IdentifiedObjectInterface::class)
        );
    }

    public function testInvokeBadObject()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(DeletingService::class),
            new \stdClass()
        );
    }

    public function testInvoke()
    {
        self::assertInstanceOf(
            DeleteObject::class,
            $this->buildStep()(
                $this->createMock(DeletingService::class),
                $this->createMock(IdentifiedObjectInterface::class)
            )
        );
    }
}

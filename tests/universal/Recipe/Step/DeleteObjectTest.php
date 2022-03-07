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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\DeleteObject;
use Teknoo\East\Website\Service\DeletingService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\DeleteObject
 */
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
            $this->createMock(ObjectInterface::class)
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
                $this->createMock(ObjectInterface::class)
            )
        );
    }
}

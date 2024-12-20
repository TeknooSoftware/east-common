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
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Recipe\Step\ExtractPage;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ExtractPage::class)]
class ExtractPageTest extends TestCase
{
    public function buildStep(): ExtractPage
    {
        return new ExtractPage();
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            '1'
        );
    }

    public function testInvokeBadPage()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ManagerInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeWithoutPage()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'page' => 1
        ]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractPage::class,
            $this->buildStep()(
                $manager
            )
        );
    }

    public function testInvokeWithPage()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'page' => 2
        ]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractPage::class,
            $this->buildStep()(
                $manager,
                '2'
            )
        );
    }

    public function testInvokeWithInvalidPage()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('updateWorkPlan')->with([
            'page' => 1
        ]);
        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            ExtractPage::class,
            $this->buildStep()(
                $manager,
                'foo'
            )
        );
    }
}

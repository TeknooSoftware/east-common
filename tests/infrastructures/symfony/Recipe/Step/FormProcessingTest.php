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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FormProcessing::class)]
class FormProcessingTest extends TestCase
{
    public function buildStep(): FormProcessing
    {
        return new FormProcessing();
    }

    public function testInvokeFormNotSubmitted()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('continue')->with([], 'nextStep');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())->method('isSubmitted')->willReturn(false);
        $form->expects($this->any())->method('isValid')->willReturn(false);

        self::assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }

    public function testInvokeFormNotValid()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('continue')->with([], 'nextStep');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())->method('isSubmitted')->willReturn(true);
        $form->expects($this->any())->method('isValid')->willReturn(false);

        self::assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }

    public function testInvokeFormSubmittedAndValid()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())->method('isSubmitted')->willReturn(true);
        $form->expects($this->any())->method('isValid')->willReturn(true);

        self::assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }
}

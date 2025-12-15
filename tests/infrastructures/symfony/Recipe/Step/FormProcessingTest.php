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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FormProcessing::class)]
class FormProcessingTest extends TestCase
{
    public function buildStep(): FormProcessing
    {
        return new FormProcessing(true);
    }

    public function testInvokeFormNotSubmitted(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('continue')->with([], 'nextStep');

        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);

        $this->assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }

    public function testInvokeFormNotValid(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->once())->method('continue')->with([], 'nextStep');

        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(false);

        $this->assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }

    public function testInvokeFormSubmittedAndValid(): void
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects($this->never())->method('continue');

        $form = $this->createStub(FormInterface::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);

        $this->assertInstanceOf(
            FormProcessing::class,
            $this->buildStep()(
                $form,
                $manager,
                'nextStep'
            )
        );
    }
}

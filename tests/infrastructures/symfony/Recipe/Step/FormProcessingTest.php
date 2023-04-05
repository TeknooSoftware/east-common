<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\CommonBundle\Recipe\Step\FormProcessing;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Recipe\Step\FormProcessing
 */
class FormProcessingTest extends TestCase
{
    public function buildStep(): FormProcessing
    {
        return new FormProcessing();
    }

    public function testInvokeFormNotSubmitted()
    {
        $manager = $this->createMock(ManagerInterface::class);
        $manager->expects(self::once())->method('continue')->with([], 'nextStep');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(false);
        $form->expects(self::any())->method('isValid')->willReturn(false);

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
        $manager->expects(self::once())->method('continue')->with([], 'nextStep');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(false);

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
        $manager->expects(self::never())->method('continue');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

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

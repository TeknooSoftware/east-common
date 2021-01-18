<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\WebsiteBundle\Recipe\Step\FormProcessing;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Recipe\Step\FormProcessing
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

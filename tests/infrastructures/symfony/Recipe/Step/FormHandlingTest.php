<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Common\Service\DatesService;
use Teknoo\East\CommonBundle\Recipe\Step\FormHandling;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers      \Teknoo\East\CommonBundle\Recipe\Step\FormHandling
 */
class FormHandlingTest extends TestCase
{
    private ?DatesService $datesService = null;

    private ?FormFactory $formFactory = null;

    /**
     * @return DatesService|MockObject
     */
    private function getDatesService(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    /**
     * @return FormFactory|MockObject
     */
    private function getFormFactory(): FormFactory
    {
        if (!$this->formFactory instanceof FormFactory) {
            $this->formFactory = $this->createMock(FormFactory::class);
        }

        return $this->formFactory;
    }

    public function buildStep(): FormHandling
    {
        return new FormHandling(
            $this->getDatesService(),
            $this->getFormFactory()
        );
    }

    public function testInvokeWithPublishableAndNotPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects(self::any())->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('handleRequest');

        $this->getFormFactory()
            ->expects(self::any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects(self::never())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $formOptions,
                $object
            )
        );
    }

    public function testInvokeWithPublishableAndNotPublishWithHandlingDisabled()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects(self::any())->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::never())->method('handleRequest');

        $this->getFormFactory()
            ->expects(self::any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects(self::never())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $formOptions,
                $object,
                false
            )
        );
    }

    public function testInvokeWithNotPublishableAndNotPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects(self::any())->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects(self::never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('handleRequest');

        $this->getFormFactory()
            ->expects(self::any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $formOptions,
                $object
            )
        );
    }

    public function testInvokeWithPublishableAndPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects(self::any())->method('getParsedBody')->willReturn([
            'publish' => true
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = new class implements IdentifiedObjectInterface, PublishableInterface {
            public function getId(): string {}

            public function getPublishedAt(): ?\DateTimeInterface {}

            public function setPublishedAt(\DateTimeInterface $dateTime): PublishableInterface {}
        };

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('handleRequest');

        $this->getFormFactory()
            ->expects(self::any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects(self::once())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $formOptions,
                $object
            )
        );
    }

    public function testInvokeWithNotPublishableAndPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects(self::any())->method('getParsedBody')->willReturn([
            'publish' => true
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects(self::never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::once())->method('handleRequest');

        $this->getFormFactory()
            ->expects(self::any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $formOptions,
                $object
            )
        );
    }
}

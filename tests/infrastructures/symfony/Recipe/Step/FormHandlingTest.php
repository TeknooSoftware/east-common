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

use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\CommonBundle\Contracts\Form\FormManagerAwareInterface;
use Teknoo\East\CommonBundle\Form\Type\UserType;
use Teknoo\East\CommonBundle\Recipe\Step\FormHandling;
use Teknoo\East\Foundation\Manager\ManagerInterface;

use Teknoo\East\Foundation\Time\DatesService;
use function json_encode;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FormHandling::class)]
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

    public function testInvokeBasic()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );

        $request->expects($this->any())->method('getParsedBody')->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeWithManagerAware()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );

        $request->expects($this->any())->method('getParsedBody')->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = (new class extends AbstractType implements FormManagerAwareInterface {})::class;
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeWithPublishableAndNotPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );

        $request->expects($this->any())->method('getParsedBody')->willReturn([
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];

        $object = $this->createMock(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeWithPublishableAndNotPublishWithHandlingDisabled()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
                false
            )
        );
    }

    public function testInvokeWithPApiWithHandlingDisabled()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                'api' => 'json',
                default => false,
            }
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
                false
            )
        );
    }

    public function testInvokeWithNotPublishableAndNotPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeWithPublishableAndPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );

        $request->expects($this->any())->method('getParsedBody')->willReturn([
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
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->once())
            ->method('passMeTheDate');

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeWithNotPublishableAndPublish()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                default => false,
            }
        );

        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'publish' => true
        ]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeApiNoJson()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->expects($this->any())->method('getMethod')->willReturn('POST');
        $request->expects($this->any())->method('getParsedBody')->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeApiNoJsonWithFormApiAware()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->expects($this->any())->method('getMethod')->willReturn('POST');
        $request->expects($this->any())->method('getParsedBody')->willReturn([]);

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = UserType::class;
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false, 'api' => 'json'],
            )
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    private function runTestForInvokeApiWithJson(string $method): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->expects($this->any())->method('getHeader')->willReturnCallback(
            fn (string $header) => match ($header) {
                'Content-Type' => ['application/json'],
                default => [],
            }
        );

        $request->expects($this->any())->method('getMethod')->willReturn($method);
        $request->expects($this->any())->method('getParsedBody')->willReturn([]);
        $body = ['foo' => 'bar', 'bar' => ['foo']];
        $request->expects($this->any())->method('getBody')->willReturn(
            (new StreamFactory())->createStream(json_encode($body))
        );

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit')->with($body, false);

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }

    public function testInvokeApiWithJsonAndPOSTMethod()
    {
        $this->runTestForInvokeApiWithJson('POST');
    }

    public function testInvokeApiWithJsonAndPUTMethod()
    {
        $this->runTestForInvokeApiWithJson('PUT');
    }

    public function testInvokeApiWithJsonAndPATCHMethod()
    {
        $this->runTestForInvokeApiWithJson('PATCH');
    }

    public function testInvokeApiWithJsonAndGETMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturnCallback(
            fn (string $name) => match ($name) {
                'request' => $this->createMock(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->expects($this->any())->method('getHeader')->willReturnCallback(
            fn (string $header) => match ($header) {
                'Content-Type' => ['application/json'],
                default => [],
            }
        );

        $request->expects($this->any())->method('getMethod')->willReturn('Get');
        $request->expects($this->any())->method('getParsedBody')->willReturn([]);
        $body = ['foo' => 'bar', 'bar' => ['foo']];
        $request->expects($this->any())->method('getBody')->willReturn(
            (new StreamFactory())->createStream(json_encode($body))
        );

        $manager = $this->createMock(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createMock(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory()
            ->expects($this->any())
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        self::assertInstanceOf(
            FormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $formClass,
                $object,
                $formOptions,
            )
        );
    }
}

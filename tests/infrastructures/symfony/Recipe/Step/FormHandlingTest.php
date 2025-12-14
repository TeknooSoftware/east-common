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

use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FormHandling::class)]
class FormHandlingTest extends TestCase
{
    private (DatesService&Stub)|(DatesService&MockObject)|null $datesService = null;

    private (FormFactory&Stub)|(FormFactory&MockObject)|null $formFactory = null;

    private function getDatesService(bool $stub = false): (DatesService&Stub)|(DatesService&MockObject)
    {
        if (!$this->datesService instanceof DatesService) {
            if ($stub) {
                $this->datesService = $this->createStub(DatesService::class);
            } else {
                $this->datesService = $this->createMock(DatesService::class);
            }
        }

        return $this->datesService;
    }

    private function getFormFactory(bool $stub = false): (FormFactory&Stub)|(FormFactory&MockObject)
    {
        if (!$this->formFactory instanceof FormFactory) {
            if ($stub) {
                $this->formFactory = $this->createStub(FormFactory::class);
            } else {
                $this->formFactory = $this->createMock(FormFactory::class);
            }
        }

        return $this->formFactory;
    }

    public function buildStep(): FormHandling
    {
        return new FormHandling(
            $this->getDatesService(true),
            $this->getFormFactory(true)
        );
    }

    public function testInvokeBasic(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );

        $request->method('getParsedBody')->willReturn([]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeWithManagerAware(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );

        $request->method('getParsedBody')->willReturn([]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = (new class () extends AbstractType implements FormManagerAwareInterface {})::class;
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeWithPublishableAndNotPublish(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );

        $request->method('getParsedBody')->willReturn([
        ]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];

        $object = $this->createStub(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $this->assertInstanceOf(
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

    public function testInvokeWithPublishableAndNotPublishWithHandlingDisabled(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );
        $request->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $this->assertInstanceOf(
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

    public function testInvokeWithPApiWithHandlingDisabled(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|string|false => match ($name) {
                'request' => $this->createStub(Request::class),
                'api' => 'json',
                default => false,
            }
        );
        $request->method('getParsedBody')->willReturn([]);
        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->never())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $this->assertInstanceOf(
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

    public function testInvokeWithNotPublishableAndNotPublish(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );
        $request->method('getParsedBody')->willReturn([
        ]);
        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeWithPublishableAndPublish(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );

        $request->method('getParsedBody')->willReturn([
            'publish' => true
        ]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];

        $object = new class () implements IdentifiedObjectInterface, PublishableInterface {
            public function getId(): string
            {
            }

            public function getPublishedAt(): ?\DateTimeInterface
            {
            }

            public function setPublishedAt(\DateTimeInterface $dateTime): PublishableInterface
            {
            }
        };

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->getDatesService()
            ->expects($this->once())
            ->method('passMeTheDate');

        $this->assertInstanceOf(
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

    public function testInvokeWithNotPublishableAndPublish(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|false => match ($name) {
                'request' => $this->createStub(Request::class),
                default => false,
            }
        );

        $request->method('getParsedBody')->willReturn([
            'publish' => true
        ]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeApiNoJson(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|string|false => match ($name) {
                'request' => $this->createStub(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn([]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeApiNoJsonWithFormApiAware(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|string|false => match ($name) {
                'request' => $this->createStub(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->method('getMethod')->willReturn('POST');
        $request->method('getParsedBody')->willReturn([]);

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = UserType::class;
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false, 'api' => 'json'],
            )
            ->willReturn($form);

        $this->assertInstanceOf(
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
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|string|false => match ($name) {
                'request' => $this->createStub(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->method('getHeader')->willReturnCallback(
            fn (string $header): array => match ($header) {
                'Content-Type' => ['application/json'],
                default => [],
            }
        );

        $request->method('getMethod')->willReturn($method);
        $request->method('getParsedBody')->willReturn([]);
        $body = ['foo' => 'bar', 'bar' => ['foo']];
        $request->method('getBody')->willReturn(
            new StreamFactory()->createStream(json_encode($body))
        );

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('submit')->with($body, false);

        $this->getFormFactory(true)
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        $this->assertInstanceOf(
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

    public function testInvokeApiWithJsonAndPOSTMethod(): void
    {
        $this->runTestForInvokeApiWithJson('POST');
    }

    public function testInvokeApiWithJsonAndPUTMethod(): void
    {
        $this->runTestForInvokeApiWithJson('PUT');
    }

    public function testInvokeApiWithJsonAndPATCHMethod(): void
    {
        $this->runTestForInvokeApiWithJson('PATCH');
    }

    public function testInvokeApiWithJsonAndGETMethod(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturnCallback(
            fn (string $name): Stub|string|false => match ($name) {
                'request' => $this->createStub(Request::class),
                'api' => 'json',
                default => false,
            }
        );

        $request->method('getHeader')->willReturnCallback(
            fn (string $header): array => match ($header) {
                'Content-Type' => ['application/json'],
                default => [],
            }
        );

        $request->method('getMethod')->willReturn('Get');
        $request->method('getParsedBody')->willReturn([]);
        $body = ['foo' => 'bar', 'bar' => ['foo']];
        $request->method('getBody')->willReturn(
            new StreamFactory()->createStream(json_encode($body))
        );

        $manager = $this->createStub(ManagerInterface::class);
        $formClass = 'Foo/Bar';
        $formOptions = [];
        $object = $this->createStub(IdentifiedObjectInterface::class);

        $this->getDatesService()
            ->expects($this->never())
            ->method('passMeTheDate');

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->never())->method('submit');

        $this->getFormFactory(true)
            ->method('create')
            ->with(
                $formClass,
                $object,
                ['csrf_protection' => false],
            )
            ->willReturn($form);

        $this->assertInstanceOf(
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

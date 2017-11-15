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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminNewEndPoint;
use Teknoo\East\WebsiteBundle\Form\Type\TypeType;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminNewEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminFormTrait
 */
class AdminNewEndPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WriterInterface
     */
    private $writerService;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @return WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getWriterService(): WriterInterface
    {
        if (!$this->writerService instanceof WriterInterface) {
            $this->writerService = $this->createMock(WriterInterface::class);
        }

        return $this->writerService;
    }

    /**
     * @return \Twig_Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getTwig(): \Twig_Environment
    {
        if (!$this->twig instanceof \Twig_Environment) {
            $this->twig = $this->createMock(\Twig_Environment::class);
        }

        return $this->twig;
    }

    /**
     * @return RouterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRouter(): RouterInterface
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    /**
     * @return FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getFormFactory(): FormFactory
    {
        if (!$this->formFactory instanceof FormFactory) {
            $this->formFactory = $this->createMock(FormFactory::class);
        }

        return $this->formFactory;
    }

    public function buildEndPoint()
    {
        return (new AdminNewEndPoint())
            ->setWriter($this->getWriterService())
            ->setTwig($this->getTwig())
            ->setRouter($this->getRouter())
            ->setFormFactory($this->getFormFactory())
            ->setFormClass(TypeType::class)
            ->setObjectClass(Type::class)
            ->setViewPath('foo:bar.html.twig');;
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadRequest()
    {
        ($this->buildEndPoint())(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            true,
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadClient()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            'foo',
            true,
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadId()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            true,
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadTranslatable()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new \stdClass(),
            'bar'
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnInvokeWithBadRoute()
    {
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            true,
            new \stdClass()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testExceptionOnSetObjectClassWithBadName()
    {
        (new AdminNewEndPoint())->setObjectClass('foo');
    }

    /**
     * @expectedException \LogicException
     */
    public function testExceptionOnSetFormClassWithBadName()
    {
        (new AdminNewEndPoint())->setFormClass('foo');
    }

    public function testInvokeNotSubmitted()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $form = $this->createMock(FormInterface::class);
        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->with(TypeType::class)
            ->willReturn($form);

        $this->getWriterService()
            ->expects(self::never())
            ->method('save');

        $this->getRouter()
            ->expects(self::never())
            ->method('generate');

        self::assertInstanceOf(
            AdminNewEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->with(TypeType::class)
            ->willReturn($form);

        $this->getWriterService()
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(function ($object, PromiseInterface $promise) {
                self::assertInstanceOf(Type::class, $object);
                $promise->fail(new \Exception());

                return $this->getWriterService();
            });

        $this->getRouter()
            ->expects(self::never())
            ->method('generate');

        self::assertInstanceOf(
            AdminNewEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedSuccess()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->with(TypeType::class)
            ->willReturn($form);

        $this->getWriterService()
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(function ($object, PromiseInterface $promise) {
                self::assertInstanceOf(Type::class, $object);
                $promise->success($object);

                return $this->getWriterService();
            });

        $this->getRouter()
            ->expects(self::once())
            ->method('generate')
            ->with('foo')
            ->willReturn('bar');

        self::assertInstanceOf(
            AdminNewEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }
}

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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Website\Doctrine\Form\Type\ContentType;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Service\FindSlugService;
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
class AdminNewEndPointTest extends TestCase
{
    /**
     * @var WriterInterface
     */
    private $writerService;

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @return WriterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWriterService(): WriterInterface
    {
        if (!$this->writerService instanceof WriterInterface) {
            $this->writerService = $this->createMock(WriterInterface::class);
        }

        return $this->writerService;
    }

    /**
     * @return EngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getEngine(): EngineInterface
    {
        if (!$this->engine instanceof EngineInterface) {
            $this->engine = $this->createMock(EngineInterface::class);
        }

        return $this->engine;
    }

    /**
     * @return RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getRouter(): RouterInterface
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    /**
     * @return FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getFormFactory(): FormFactory
    {
        if (!$this->formFactory instanceof FormFactory) {
            $this->formFactory = $this->createMock(FormFactory::class);
        }

        return $this->formFactory;
    }

    public function buildEndPoint(string $formClass = TypeType::class, string $objectClass = Type::class)
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::any())->method('withHeader')->willReturnSelf();
        $response->expects(self::any())->method('withBody')->willReturnSelf();
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::any())->method('createResponse')->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::any())->method('createStream')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromFile')->willReturn($stream);
        $streamFactory->expects(self::any())->method('createStreamFromResource')->willReturn($stream);

        return (new AdminNewEndPoint())
            ->setWriter($this->getWriterService())
            ->setTemplating($this->getEngine())
            ->setRouter($this->getRouter())
            ->setFormFactory($this->getFormFactory())
            ->setFormClass($formClass)
            ->setObjectClass($objectClass)
            ->setResponseFactory($responseFactory)
            ->setStreamFactory($streamFactory)
            ->setLoader($this->createMock(LoaderInterface::class))
            ->setFindSlugService($this->createMock(FindSlugService::class), 'slug')
            ->setViewPath('foo:bar.html.engine');
        ;
    }

    public function testSetFormOptions()
    {
        self::assertInstanceOf(
            AdminNewEndPoint::class,
            $this->buildEndPoint()
                ->setFormOptions(['doctrine_type' => ChoiceType::class])
        );
    }

    public function testExceptionOnInvokeWithBadRequest()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            new \stdClass(),
            $this->createMock(ClientInterface::class),
            'foo',
            true,
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadClient()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            'foo',
            true,
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadId()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            new \stdClass(),
            true,
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadTranslatable()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            new \stdClass(),
            'bar'
        );
    }

    public function testExceptionOnInvokeWithBadRoute()
    {
        $this->expectException(\TypeError::class);
        ($this->buildEndPoint())(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ClientInterface::class),
            'foo',
            true,
            new \stdClass()
        );
    }

    public function testExceptionOnSetObjectClassWithBadName()
    {
        $this->expectException(\LogicException::class);
        (new AdminNewEndPoint())->setObjectClass('foo');
    }

    public function testExceptionOnSetFormClassWithBadName()
    {
        $this->expectException(\LogicException::class);
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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(function (PromiseInterface $promise) {
                $result = $this->createMock(ResultInterface::class);
                $result->expects(self::any())->method('__toString')->willReturn('foo');
                $promise->success($result);

                return $this->getEngine();
            });

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

    public function testInvokeSubmittedSuccessWithSluggableObject()
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
            ->with(ContentType::class)
            ->willReturn($form);

        $this->getWriterService()
            ->expects(self::once())
            ->method('save')
            ->willReturnCallback(function ($object, PromiseInterface $promise) {
                self::assertInstanceOf(Content::class, $object);
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
            ($this->buildEndPoint(ContentType::class, Content::class))($request, $client, 'foo')
        );
    }
}

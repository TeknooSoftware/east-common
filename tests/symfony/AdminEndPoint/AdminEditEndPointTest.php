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
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEditEndPoint;
use Teknoo\East\WebsiteBundle\Form\Type\ContentType;
use Teknoo\East\WebsiteBundle\Form\Type\TypeType;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEditEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminFormTrait
 */
class AdminEditEndPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LoaderInterface
     */
    private $loaderService;

    /**
     * @var WriterInterface
     */
    private $writerService;

    /**
     * @var EngineInterface
     */
    private $twig;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @return LoaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoaderService(): LoaderInterface
    {
        if (!$this->loaderService instanceof LoaderInterface) {
            $this->loaderService = $this->createMock(LoaderInterface::class);
        }

        return $this->loaderService;
    }

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
     * @return EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getTwig(): EngineInterface
    {
        if (!$this->twig instanceof EngineInterface) {
            $this->twig = $this->createMock(EngineInterface::class);
        }

        return $this->twig;
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

    public function buildEndPoint($formClass = TypeType::class)
    {
        return (new AdminEditEndPoint())
            ->setWriter($this->getWriterService())
            ->setLoader($this->getLoaderService())
            ->setFormFactory($this->getFormFactory())
            ->setTemplating($this->getTwig())
            ->setFormClass($formClass)
            ->setViewPath('foo:bar.html.twig');
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnSetCurrentDateWithBadInstance()
    {
        $this->buildEndPoint()->setCurrentDate(new \stdClass());
    }

    public function testSetCurrentDateWithDateTime()
    {
        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $this->buildEndPoint()
                ->setCurrentDate(new \DateTime('2017-11-01'))
        );
    }

    public function testSetCurrentDateWithDateTimeImmutable()
    {
        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $this->buildEndPoint()
                ->setCurrentDate(new \DateTimeImmutable('2017-11-01'))
        );
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
    public function testExceptionOnSetFormClassWithBadName()
    {
        (new AdminEditEndPoint())->setFormClass('foo');
    }

    public function testInvokeNotFound()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) {
                self::assertEquals('foo', $criteria);
                $promise->fail(new \DomainException());

                return $this->getLoaderService();
            });

        $this->getWriterService()
            ->expects(self::never())
            ->method('save');

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeNotSubmitted()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) {
                self::assertEquals('foo', $criteria);
                $promise->success(new Type());

                return $this->getLoaderService();
            });

        $form = $this->createMock(FormInterface::class);
        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->with(TypeType::class)
            ->willReturn($form);

        $this->getWriterService()
            ->expects(self::never())
            ->method('save');

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedError()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::never())->method('acceptResponse');
        $client->expects(self::once())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) {
                self::assertEquals('foo', $criteria);
                $promise->success(new Type());

                return $this->getLoaderService();
            });

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

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedSuccess()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) {
                self::assertEquals('foo', $criteria);
                $promise->success(new Type());

                return $this->getLoaderService();
            });

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::atLeastOnce())
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

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            ($this->buildEndPoint())($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedSuccessWithPublishedContentNotPublishing()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $content = $this->createMock(Content::class);
        $content->expects(self::never())->method('__call');

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($content) {
                self::assertEquals('foo', $criteria);
                $promise->success($content);

                return $this->getLoaderService();
            });

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::atLeastOnce())
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

        $endPoint = $this->buildEndPoint(ContentType::class);

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint
                ->setCurrentDate(new \DateTimeImmutable('2017-11-01'))
        );

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedSuccessWithPublishedContentPublishing()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getParsedBody')
            ->willReturn(['publish' => '']);

        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $content = $this->createMock(Content::class);
        $content->expects(self::once())
            ->method('__call')
            ->with('setPublishedAt', [new \DateTimeImmutable('2017-11-01')])
            ->willReturnSelf();

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($content) {
                self::assertEquals('foo', $criteria);
                $promise->success($content);

                return $this->getLoaderService();
            });

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::atLeastOnce())
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

        $endPoint = $this->buildEndPoint(ContentType::class);

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint
                ->setCurrentDate(new \DateTimeImmutable('2017-11-01'))
        );

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint($request, $client, 'foo')
        );
    }

    public function testInvokeSubmittedSuccessWithPublishedContentPublishingWithNoDateMock()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getParsedBody')
            ->willReturn(['publish' => '']);

        $client = $this->createMock(ClientInterface::class);

        $client->expects(self::once())->method('acceptResponse');
        $client->expects(self::never())->method('errorInRequest');

        $content = $this->createMock(Content::class);
        $content->expects(self::once())
            ->method('__call')
            ->with('setPublishedAt')
            ->willReturnSelf();

        $this->getLoaderService()
            ->expects(self::once())
            ->method('load')
            ->willReturnCallback(function ($criteria, PromiseInterface $promise) use ($content) {
                self::assertEquals('foo', $criteria);
                $promise->success($content);

                return $this->getLoaderService();
            });

        $form = $this->createMock(FormInterface::class);
        $form->expects(self::any())->method('isSubmitted')->willReturn(true);
        $form->expects(self::any())->method('isValid')->willReturn(true);

        $this->getFormFactory()
            ->expects(self::atLeastOnce())
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

        $endPoint = $this->buildEndPoint(ContentType::class);

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint($request, $client, 'foo')
        );
    }
}

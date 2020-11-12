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
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Service\DatesService;
use Teknoo\East\Website\Service\FindSlugService;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEditEndPoint;
use Teknoo\East\Website\Doctrine\Form\Type\ContentType;
use Teknoo\East\WebsiteBundle\Form\Type\TypeType;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEditEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminFormTrait
 */
class AdminEditEndPointTest extends TestCase
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
     * @var DatesService
     */
    private $datesService;

    /**
     * @var FindSlugService
     */
    private $findSlugService;

    /**
     * @return LoaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLoaderService(): LoaderInterface
    {
        if (!$this->loaderService instanceof LoaderInterface) {
            $this->loaderService = $this->createMock(LoaderInterface::class);
        }

        return $this->loaderService;
    }

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
        if (!$this->twig instanceof EngineInterface) {
            $this->twig = $this->createMock(EngineInterface::class);
        }

        return $this->twig;
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

    /**
     * @return DatesService|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDatesService(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    /**
     * @return FindSlugService|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getFindSlugService(): FindSlugService
    {
        if (!$this->findSlugService instanceof FindSlugService) {
            $this->findSlugService = $this->createMock(FindSlugService::class);
        }

        return $this->findSlugService;
    }

    public function buildEndPoint($formClass = TypeType::class)
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

        return (new AdminEditEndPoint())
            ->setWriter($this->getWriterService())
            ->setLoader($this->getLoaderService())
            ->setFormFactory($this->getFormFactory())
            ->setTemplating($this->getEngine())
            ->setResponseFactory($responseFactory)
            ->setStreamFactory($streamFactory)
            ->setFormClass($formClass)
            ->setDatesService($this->getDatesService())
            ->setFindSlugService($this->getFindSlugService(), 'slug')
            ->setViewPath('foo:bar.html.twig');
    }

    public function testExceptionOnSetDatesServiceWithBadInstance()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()->setDatesService(new \stdClass());
    }

    public function testSetDatesService()
    {
        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $this->buildEndPoint()
                ->setDatesService($this->getDatesService())
        );
    }

    public function testSetFormOptions()
    {
        self::assertInstanceOf(
            AdminEditEndPoint::class,
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

    public function testExceptionOnSetFormClassWithBadName()
    {
        $this->expectException(\LogicException::class);
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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(function (PromiseInterface $promise) {
                $result = $this->createMock(ResultInterface::class);
                $result->expects(self::any())->method('__toString')->willReturn('foo');
                $promise->success($result);

                return $this->getEngine();
            });

        $endPoint = $this->buildEndPoint(ContentType::class);

        $this->getDatesService()
            ->expects(self::any())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callable) {
                $callable(new \DateTimeImmutable('2017-11-01'));

                return $this->getDatesService();
            });

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(function (PromiseInterface $promise) {
                $result = $this->createMock(ResultInterface::class);
                $result->expects(self::any())->method('__toString')->willReturn('foo');
                $promise->success($result);

                return $this->getEngine();
            });

        $endPoint = $this->buildEndPoint(ContentType::class);


        $this->getDatesService()
            ->expects(self::any())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callable) {
                $callable(new \DateTimeImmutable('2017-11-01'));

                return $this->getDatesService();
            });

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

        $this->getEngine()
            ->expects(self::any())
            ->method('render')
            ->willReturnCallback(function (PromiseInterface $promise) {
                $result = $this->createMock(ResultInterface::class);
                $result->expects(self::any())->method('__toString')->willReturn('foo');
                $promise->success($result);

                return $this->getEngine();
            });

        $endPoint = $this->buildEndPoint(ContentType::class);

        $this->getDatesService()
            ->expects(self::any())
            ->method('passMeTheDate')
            ->willReturnCallback(function ($callable) {
                $callable(new \DateTime());

                return $this->getDatesService();
            });

        self::assertInstanceOf(
            AdminEditEndPoint::class,
            $endPoint($request, $client, 'foo')
        );
    }
}

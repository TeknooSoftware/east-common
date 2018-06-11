<?php

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\Recipe\RecipeInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\EndPoint\ContentEndPointTrait;
use Teknoo\East\Website\EndPoint\StaticEndPointTrait;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\Object\Media;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Type;
use Teknoo\East\Website\Object\Block;
use Zend\Diactoros\ServerRequest;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var MediaLoader
     */
    private $mediaLoader;

    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @var MediaEndPointTrait|EndPointInterface
     */
    private $mediaEndPoint;

    /**
     * @var ContentEndPointTrait|EndPointInterface
     */
    private $contentEndPoint;

    /**
     * @var StaticEndPointTrait|EndPointInterface
     */
    private $staticEndPoint;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var string
     */
    public $templateToCall;

    /**
     * @var string
     */
    public $templateContent;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @va \Throwable
     */
    public $error;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I have DI initialized
     */
    public function iHaveDiInitialized()
    {
        $containerDefinition = new \DI\ContainerBuilder();
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/vendor/teknoo/east-foundation/src/universal/di.php'
        );
        $containerDefinition->addDefinitions(
            include \dirname(\dirname(__DIR__)).'/src/universal/di.php'
        );

        $this->container = $containerDefinition->build();

        $this->container->set(ObjectManager::class, $this->buildObjectManager());
    }

    public function buildObjectManager(): ObjectManager
    {
        return new class($this) implements ObjectManager {
            private $featureContext;

            /**
             *  constructor.
             * @param FeatureContext $featureContext
             */
            public function __construct(FeatureContext $featureContext)
            {
                $this->featureContext = $featureContext;
            }

            public function find($className, $id)
            {
            }

            public function persist($object)
            {
            }

            public function remove($object)
            {
            }

            public function merge($object)
            {
            }

            public function clear($objectName = null)
            {
            }

            public function detach($object)
            {
            }

            public function refresh($object)
            {
            }

            public function flush()
            {
            }

            public function getRepository($className)
            {
                return $this->featureContext->buildObjectRepository($className);
            }

            public function getClassMetadata($className)
            {
            }

            public function getMetadataFactory()
            {
            }

            public function initializeObject($obj)
            {
            }

            public function contains($object)
            {
            }
        };
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    public function buildObjectRepository(string $className): ObjectRepository
    {
        $this->objectRepository = new class($className) implements ObjectRepository {
            /**
             * @var string
             */
            private $className;

            /**
             * @var object
             */
            private $object;

            /**
             * @var array
             */
            private $criteria;

            /**
             *  constructor.
             * @param string $className
             */
            public function __construct(string $className)
            {
                $this->className = $className;
            }

            /**
             * @param array $criteria
             * @param object $object
             * @return $this
             */
            public function setObject(array $criteria, $object): self
            {
                $this->criteria = $criteria;
                $this->object = $object;

                return $this;
            }

            public function find($id)
            {
                // TODO: Implement find() method.
            }

            public function findAll()
            {
                // TODO: Implement findAll() method.
            }

            public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
            {
            }

            public function findOneBy(array $criteria)
            {
                if (\array_key_exists('deletedAt', $criteria)) {
                    unset($criteria['deletedAt']);
                }
                
                if (isset($criteria['slug']) && 'page-with-error' == $criteria['slug']) {
                    throw new \Exception('Error');
                }

                if ($this->criteria == $criteria) {
                    return $this->object;
                }

                return null;
            }

            public function getClassName()
            {
                return $this->className;
            }
        };

        return $this->objectRepository;
    }

    /**
     * @Given a Media Loader
     */
    public function aMediaLoader()
    {
        $this->mediaLoader = new MediaLoader(
            $this->buildObjectRepository(Media::class)
        );
    }

    /**
     * @Given an available image called :arg1
     */
    public function anAvailableImageCalled($arg1)
    {
        $resource = new class() {
            public function getResource()
            {
                $hf = \fopen('php://memory', 'rw');
                fwrite($hf, 'fooBar');
                fseek($hf, 0);

                return $hf;
            }
        };
        $this->objectRepository->setObject(
            ['id' => $arg1],
            (new Media())->setId($arg1)
                ->setName($arg1)
                ->setFile($resource)
        );
    }

    /**
     * @Given a Endpoint able to serve resource from Mongo.
     */
    public function aEndpointAbleToServeResourceFromMongo()
    {
        $this->mediaEndPoint = new class($this->mediaLoader) implements EndPointInterface {
            use EastEndPointTrait;
            use MediaEndPointTrait;
        };
    }

    /**
     * @Given I register a router
     */
    public function iRegisterARouter()
    {
        $this->router = new class implements RouterInterface {
            private $routes = [];
            private $params = [];

            public function registerRoute(string $route, callable $controller, array $params = []): self
            {
                $this->routes[$route] = $controller;
                $this->params[$route] = $params;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function execute(
                ClientInterface $client,
                ServerRequestInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                $path = $request->getUri()->getPath();

                foreach ($this->routes as $route => $endpoint) {
                    $values = [];
                    if (\preg_match($route, $path, $values)) {
                        $result = new Result($endpoint);
                        $request = $request->withAttribute(RouterInterface::ROUTER_RESULT_KEY, $result);

                        foreach ($values as $key=>$value) {
                            if (!\is_numeric($key)) {
                                $request = $request->withAttribute($key, $value);
                            }
                        }

                        foreach ($this->params[$route] as $key=>$value) {
                            $request = $request->withAttribute($key, $value);
                        }

                        $manager->updateWorkPlan([\Teknoo\East\Foundation\Router\ResultInterface::class => $result]);

                        $manager->continueExecution($client, $request);

                        break;
                    }
                }

                return $this;
            }
        };

        $this->container->set(RouterInterface::class, $this->router);
    }

    /**
     * @Given The router can process the request :arg1 to controller :arg2
     */
    public function theRouterCanProcessTheRequestToController($arg1, $arg2)
    {
        switch ($arg2) {
            case 'contentEndPoint':
                $controller = $this->contentEndPoint;
                $this->router->registerRoute($arg1, $controller);
                break;
            case 'staticEndPoint':
                $controller = $this->staticEndPoint;
                $this->router->registerRoute($arg1, $controller, ['template' => 'Acme:MyBundle:template.html.twig']);
                break;
            case 'mediaEndPoint':
                $controller = $this->mediaEndPoint;
                $this->router->registerRoute($arg1, $controller);
                break;
        }
    }

    /**
     * @When The server will receive the request :arg1
     */
    public function theServerWillReceiveTheRequest($arg1)
    {
        $manager = new Manager($this->container->get(RecipeInterface::class));

        $this->response = null;
        $this->error = null;

        $this->client = new class($this) implements ClientInterface {
            /**
             * @var FeatureContext
             */
            private $context;

            /**
             *  constructor.
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            /**
             * @inheritDoc
             */
            public function updateResponse(callable $modifier): ClientInterface
            {
                $modifier($this, $this->context->response);

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function acceptResponse(ResponseInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(ResponseInterface $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }
        };

        $request = new ServerRequest();
        $request = $request->withUri(new \Zend\Diactoros\Uri($arg1));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);

        $manager->receiveRequest(
            $this->client,
            $request
        );
    }

    /**
     * @Then The client must accept a response
     */
    public function theClientMustAcceptAResponse()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then I should get :arg1
     */
    public function iShouldGet($arg1)
    {
        Assert::assertEquals($arg1, (string) $this->response->getBody());
    }

    /**
     * @Then The client must accept an error
     */
    public function theClientMustAcceptAnError()
    {
        Assert::assertNull($this->response);
        Assert::assertInstanceOf(\Throwable::class, $this->error);
    }

    /**
     * @Given a Content Loader
     */
    public function aContentLoader()
    {
        $this->contentLoader = new ContentLoader(
            $this->buildObjectRepository(Content::class)
        );
    }

    /**
     * @Given a type of page, called :arg1 with :arg2 blocks :arg3 and template :arg4 with :arg5
     */
    public function aTypeOfPageCalledWithBlocksAndTemplateWith($arg1, $arg2, $arg3, $arg4, $arg5)
    {
        $this->type = new Type();
        $this->type->setName($arg1);
        $blocks = [];
        foreach (\explode(',', $arg3) as $name) {
            $blocks[] = new Block($name, 'text');
        }
        $this->type->setBlocks($blocks);
        $this->type->setTemplate($arg4);
        $this->templateToCall = $arg4;
        $this->templateContent = $arg5;
    }

    /**
     * @Given an available page with the slug :arg1 of type :arg2
     */
    public function anAvailablePageWithTheSlugOfType($arg1, $arg2)
    {
        $this->objectRepository->setObject(
            ['slug' => $arg1],
            (new Content())->setSlug($arg1)
                ->setType($this->type)
                ->setParts(['block1' => 'hello', 'block2' => 'world'])
                ->setPublishedAt(new \DateTime(2017-11-25))
        );
    }

    /**
     * @Given a Endpoint able to render and serve page.
     */
    public function aEndpointAbleToRenderAndServePage()
    {
        $this->contentEndPoint = new class($this->contentLoader, '404-error') implements EndPointInterface {
            use EastEndPointTrait;
            use ContentEndPointTrait;
        };
    }

    /**
     * @Given a templating engine
     */
    public function aTemplatingEngine()
    {
        $this->templating = new class($this) implements EngineInterface {
            private $context;

            /**
             * @param FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            public function render($name, array $parameters = array())
            {
                if ('404-error' === $name) {
                    return 'Error 404';
                }
                
                Assert::assertEquals($this->context->templateToCall, $name);

                $keys = [];
                $values = [];
                if (isset($parameters['content']) && $parameters['content'] instanceof Content) {
                    foreach ($parameters['content']->getParts() as $name=>$value) {
                        $keys[] = '{'.$name.'}';
                        $values[] = $value;
                    }
                }

                return \str_replace($keys, $values, $this->context->templateContent);
            }

            public function exists($name)
            {
            }

            public function supports($name)
            {
            }
        };

        if ($this->staticEndPoint instanceof EndPointInterface) {
            $this->staticEndPoint->setTemplating($this->templating);
        }

        if ($this->contentEndPoint instanceof EndPointInterface) {
            $this->contentEndPoint->setTemplating($this->templating);
        }
    }

    /**
     * @Given a template :arg1 with :arg2
     */
    public function aTemplateWith($arg1, $arg2)
    {
        $this->templateToCall = $arg1;
        $this->templateContent = $arg2;
    }

    /**
     * @Given a Endpoint able to render and serve this template.
     */
    public function aEndpointAbleToRenderAndServeThisTemplate()
    {
        $this->staticEndPoint = new class implements EndPointInterface {
            use EastEndPointTrait;
            use StaticEndPointTrait;
        };
    }
}

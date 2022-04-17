<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Teknoo\DI\SymfonyBridge\DIBridgeBundle;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\TeknooEastCommonBundle;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Teknoo\Tests\East\Common\Behat\GetTokenStorageService;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;
use Twig\Environment;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    public ?Container $container = null;

    private ?BaseKernel $symfonyKernel = null;

    private ?RouterInterface $router = null;

    private ?ClientInterface $client = null;

    public ?ObjectRepository $objectRepository = null;

    /**
     * @var RecipeEndPoint
     */
    private ?RecipeEndPoint $mediaEndPoint = null;

    /**
     * @var RecipeEndPoint
     */
    private ?RecipeEndPoint $contentEndPoint = null;

    /**
     * @var RecipeEndPoint
     */
    private ?RecipeEndPoint $staticEndPoint = null;

    public ?EngineInterface $templating = null;

    public ?Environment $twig = null;

    public ?string $templateToCall = null;

    public ?string $templateContent = null;

    public ?ResponseInterface $response = null;

    public ?\Throwable $error = null;

    public $createdObjects = [];

    public $updatedObjects = [];

    /**
     * @Given I have DI initialized
     */
    public function iHaveDiInitialized(): void
    {
        $containerDefinition = new ContainerBuilder();
        $rootDir = dirname(__DIR__, 2);
        $containerDefinition->addDefinitions(
            include $rootDir.'/vendor/teknoo/east-foundation/src/di.php'
        );
        $containerDefinition->addDefinitions(
            include $rootDir . '/src/di.php'
        );
        $containerDefinition->addDefinitions(
            include $rootDir.'/infrastructures/doctrine/di.php'
        );
        $containerDefinition->addDefinitions(
            include $rootDir.'/infrastructures/di.php'
        );

        $this->container = $containerDefinition->build();

        $this->container->set(ObjectManager::class, $this->buildObjectManager());
    }

    /**
     * @Given I have DI With Symfony initialized
     */
    public function iHaveDiWithSymfonyInitialized(): void
    {
        $this->locale = 'en';
        $this->symfonyKernel = new class($this, 'test') extends BaseKernel
        {
            use MicroKernelTrait;

            private FeatureContext $context;

            public function __construct(FeatureContext $context, $environment)
            {
                $this->context = $context;

                parent::__construct($environment, false);
            }

            public function getProjectDir(): string
            {
                return dirname(__DIR__, 2);
            }

            public function getCacheDir(): string
            {
                return dirname(__DIR__, 2).'/tests/var/cache';
            }

            public function getLogDir(): string
            {
                return dirname(__DIR__, 2).'/tests/var/logs';
            }

            public function registerBundles(): iterable
            {
                yield new FrameworkBundle();
                yield new EastFoundationBundle();
                yield new TeknooEastCommonBundle();
                yield new DIBridgeBundle();
                yield new SecurityBundle();
            }

            protected function configureContainer(SfContainerBuilder $container, LoaderInterface $loader)
            {
                $loader->load(__DIR__.'/config/packages/*.yaml', 'glob');
                $loader->load(__DIR__.'/config/services.yaml');

                $container->setParameter('container.autowiring.strict_mode', true);
                $container->setParameter('container.dumper.inline_class_loader', true);
            }

            protected function configureRoutes($routes): void
            {
                $rootDir = dirname(__DIR__, 2);
                if ($routes instanceof RoutingConfigurator) {
                    $routes->import($rootDir . '/infrastructures/symfony/Resources/config/admin_*.yaml', 'glob')
                        ->prefix('/admin');
                    $routes->import($rootDir . '/features/bootstrap/config/routes/*.yaml', 'glob');
                    $routes->import($rootDir . '/infrastructures/symfony/Resources/config/r*.yaml', 'glob');
                } else {
                    $routes->import($rootDir . '/infrastructures/symfony/Resources/config/admin_*.yaml', '/admin', 'glob');
                    $routes->import($rootDir . '/features/bootstrap/config/routes/*.yaml', '/', 'glob');
                    $routes->import($rootDir . '/infrastructures/symfony/Resources/config/r*.yaml', '/', 'glob');
                }
            }

            protected function getContainerClass(): string
            {
                $characters = 'abcdefghijklmnopqrstuvwxyz';
                $str = '';
                for ($i = 0; $i < 10; $i++) {
                    $str .= $characters[rand(0, strlen($characters) - 1)];
                }

                return $str;
            }
        };
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

            /**
             * @param \Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface $object
             */
            public function persist($object)
            {
                if ($id = $object->getId()) {
                    $this->featureContext->updatedObjects[$id] = $object;
                } else {
                    $object->id = uniqid('eastcommon', true);
                    $this->featureContext->createdObjects[] = $object;

                    $this->featureContext->getObjectRepository()->setObject(['id' => $object->getId()], $object);
                }
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
                return $this->featureContext->getObjectRepository();
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

    public function getObjectRepository(): ObjectRepository
    {
        if (null !== $this->objectRepository) {
            return $this->objectRepository;
        }

        $this->objectRepository = new class implements ObjectRepository {
            /**
             * @var object
             */
            private $object;

            private array $criteria;

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
            }

            public function findAll()
            {
            }

            public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
            {
            }

            public function findOneBy(array $criteria)
            {
                if (\array_key_exists('deletedAt', $criteria)) {
                    unset($criteria['deletedAt']);
                }

                if (isset($criteria['or'][0]['active'])) {
                    unset($criteria['or']);
                }
                
                if (isset($criteria['slug']) && 'page-with-error' === $criteria['slug']) {
                    throw new \Exception('Error', 404);
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
     * @Given I register a router
     */
    public function iRegisterARouter(): void
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
                MessageInterface $request,
                ManagerInterface $manager
            ): MiddlewareInterface {
                if (!$request instanceof ServerRequestInterface) {
                    return $this;
                }

                $path = $request->getUri()->getPath();

                foreach ($this->routes as $route => $endpoint) {
                    $values = [];
                    if (preg_match($route, $path, $values)) {
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
     * @Given The router can process the request :url to controller :controllerName
     */
    public function theRouterCanProcessTheRequestToController(string $url, string $controllerName): void
    {
        $controller = null;
        $params = [];
        switch ($controllerName) {
            case 'staticEndPoint':
                $params = ['template' => 'Acme:MyBundle:template.html.twig'];
                $controller = $this->staticEndPoint;
                break;
        }

        if (null !== $controller) {
            $this->router->registerRoute($url, $controller, $params);
        }
    }

    private function buildClient(): ClientInterface
    {
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
            public function acceptResponse(EastResponse | MessageInterface $response): ClientInterface
            {
                $this->context->response = $response;

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function sendResponse(EastResponse | MessageInterface | null $response = null, bool $silently = false): ClientInterface
            {
                if (!empty($response)) {
                    $this->context->response = $response;
                }

                return $this;
            }

            /**
             * @inheritDoc
             */
            public function errorInRequest(\Throwable $throwable, bool $silently = false): ClientInterface
            {
                $this->context->error = $throwable;

                return $this;
            }

            public function mustSendAResponse(): ClientInterface
            {
                return $this;
            }

            public function sendAResponseIsOptional(): ClientInterface
            {
                return $this;
            }
        };

        return $this->client;
    }

    private function buildManager(ServerRequest $request): Manager
    {
        $manager = new Manager($this->container->get(CookbookInterface::class));

        $this->response = null;
        $this->error = null;

        $this->buildClient();

        $manager->receiveRequest(
            $this->client,
            $request
        );

        return $manager;
    }

    /**
     * @When The server will receive the request :url
     */
    public function theServerWillReceiveTheRequest(string $url): void
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('errorTemplate', '<error>-error');
        $request = $request->withMethod('GET');
        $request = $request->withUri(new Uri($url));
        $query = [];
        \parse_str($request->getUri()->getQuery(), $query);
        $request = $request->withQueryParams($query);

        $this->buildManager($request);
    }

    /**
     * @Then The client must accept a response
     */
    public function theClientMustAcceptAResponse(): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertNull($this->error);
    }

    /**
     * @Then I should get :body
     */
    public function iShouldGet(string $body): void
    {
        Assert::assertEquals($body, (string) $this->response->getBody());
    }

    /**
     * @Then The client must accept an error
     */
    public function theClientMustAcceptAnError(): void
    {
        Assert::assertNull($this->response);
        Assert::assertInstanceOf(\Throwable::class, $this->error);
    }

    public function buildResultObject (string $body): ResultInterface
    {
        return $result = new class ($body) implements ResultInterface {
            private string $content;

            public function __construct(string $content)
            {
                $this->content = $content;
            }

            public function __toString(): string
            {
                return $this->content;
            }
        };
    }

    /**
     * @Given a twig templating engine
     */
    public function aTwigTemplatingEngine()
    {
        $this->twig = new class extends Environment {
            public function __construct() {}

            public function render($name, array $parameters = []): string
            {
                if (empty($parameters['objectInstance'])) {
                    return 'non-object-view';
                }

                //To avoid to manage templating view for crud
                $final = [];
                $ro = new \ReflectionObject($object = $parameters['objectInstance']);
                foreach ($ro->getProperties() as $rp) {
                    if (\in_array($rp->getName(), [
                        'id',
                        'createdAt',
                        'updatedAt',
                        'deletedAt',
                        'states',
                        'activesStates',
                        'classesByStates',
                        'statesAliasesList',
                        'callerStatedClassesStack',
                        'localeField',
                        'publishedAt',
                        'defaultCallerStatedClassName',
                    ])) {
                        continue;
                    }

                    $rp->setAccessible(true);
                    $final[$rp->getName()] = $rp->getValue($object);
                }

                return json_encode($final);
            }
        };
    }

    /**
     * @Then An object must be persisted
     */
    public function anObjectMustBePersisted()
    {
        Assert::assertNotEmpty($this->createdObjects);
    }

    private function runSymfony(SFRequest $serverRequest)
    {
        $this->symfonyKernel->boot();

        $container = $this->symfonyKernel->getContainer();

        $container->set(ObjectManager::class, $this->buildObjectManager());
        $container->set('twig', $this->twig);

        $container->set(
            EngineInterface::class,
            new \Teknoo\East\Twig\Template\Engine($this->twig)
        );

        $response = $this->symfonyKernel->handle($serverRequest);

        $psrFactory = new PsrHttpFactory(
            new ServerRequestFactory(),
            new StreamFactory(),
            new UploadedFileFactory(),
            new ResponseFactory()
        );

        $this->response = $psrFactory->createResponse($response);

        $this->symfonyKernel->terminate($serverRequest, $response);
    }

    /**
     * @When the client follows the redirection
     */
    public function theClientfollowsTheRedirection()
    {
        $url = current($this->response->getHeader('location'));
        $serverRequest = SfRequest::create($url, 'GET');

        $this->runSymfony($serverRequest);
    }

    /**
     * @Then the last object updated must be deleted
     */
    public function theLastObjectUpdatedMustBeDeleted()
    {
        Assert::assertNotEmpty(current($this->updatedObjects)->getDeletedAt());
    }

    /**
     * @Then An object :id must be updated
     */
    public function anObjectMustBeUpdated(string $id)
    {
        Assert::assertNotEmpty($this->updatedObjects[$id]);
    }

    /**
     * @Then It is redirect to :url
     */
    public function itIsRedirectTo($url)
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertEquals(302, $this->response->getStatusCode());
        $location = current($this->response->getHeader('location'));

        Assert::assertGreaterThan(0, preg_match("#$url#i", $location));
    }

    /**
     * @Then I should get in the form :body
     */
    public function iShouldGetInTheForm(string $body): void
    {
        $expectedBody = json_decode($body, true);

        $actualBody = json_decode((string) $this->response->getBody(), true);

        Assert::assertEquals($expectedBody, $actualBody);
    }

    /**
     * @When Symfony will receive the POST request :url with :body
     */
    public function symfonyWillReceiveThePostRequestWith($url, $body)
    {
        $expectedBody = [];
        \parse_str($body, $expectedBody);
        $serverRequest = SfRequest::create($url, 'POST', $expectedBody);

        $this->runSymfony($serverRequest);
    }

    /**
     * @When Symfony will receive the DELETE request :url
     */
    public function symfonyWillReceiveTheDeleteRequest($url)
    {
        $serverRequest = SfRequest::create($url, 'DELETE', []);

        $this->runSymfony($serverRequest);
    }

    /**
     * @Given a object with id :id
     */
    public function aObjectOfTypeWithId($id)
    {
        $object = new MyObject($id);

        $this->getObjectRepository()->setObject(['id' => $id], $object);
    }

    /**
     * @Given a object with id :id and :properties
     */
    public function aObjectOfTypeWithIdAnd($id, $properties)
    {
        $object = new MyObject($id, $properties['name'] ?? '', $properties['slug'] ?? '');

        $this->getObjectRepository()->setObject(['id' => $id], $object);
    }

    /**
     * @Then no session must be opened
     */
    public function noSessionMustBeOpened()
    {
        $container = $this->symfonyKernel->getContainer()->get(GetTokenStorageService::class);
        if (!$container->tokenStorage) {
            Assert::fail('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $container->tokenStorage->getToken()) {
            return;
        }

        Assert::assertEmpty($token->getUser());
    }

    /**
     * @Then a session must be opened
     */
    public function aSessionMustBeOpened()
    {
        $container = $this->symfonyKernel->getContainer()->get(GetTokenStorageService::class);
        if (!$container->tokenStorage) {
            Assert::fail('The SecurityBundle is not registered in your application.');
        }

        Assert::assertNotEmpty($token = $container->tokenStorage->getToken());
        Assert::assertInstanceOf(PasswordAuthenticatedUser::class, $token->getUser());
    }

    /**
     * @Given a user with password :password
     */
    public function aUserWithPassword($password)
    {
        $this->symfonyKernel->boot();

        $object = new User;

        $storedPassword = new StoredPassword();
        $storedPassword->setAlgo(PasswordAuthenticatedUser::class)
            ->setHashedPassword(
                (new SodiumPasswordHasher())->hash($password)
            );

        $object->setId($id = 'userid');
        $object->setEmail('admin@teknoo.software')
            ->setFirstName('ad')
            ->setLastName('min')
            ->setAuthData([$storedPassword]);

        $this->getObjectRepository()->setObject(['email' => 'admin@teknoo.software'], $object);
    }
}

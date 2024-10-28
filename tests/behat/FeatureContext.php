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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Behat;

use Behat\Behat\Context\Context;
use DI\Container;
use DI\ContainerBuilder;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\Uri;
use OTPHP\TOTP;
use PHPUnit\Framework\Assert;
use ParagonIE\ConstantTime\Base32;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionObject;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SfContainerBuilder;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\Test\Constraint\EmailCount;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Symfony\Component\Routing\Router;
use Teknoo\DI\SymfonyBridge\DIBridgeBundle;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\CommonBundle\TeknooEastCommonBundle;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media as BaseMedia;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\East\FoundationBundle\EastFoundationBundle;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Client\ResponseInterface as EastResponse;
use Teknoo\East\Foundation\EndPoint\RecipeEndPoint;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Middleware\MiddlewareInterface;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\East\Foundation\Router\Result;
use Teknoo\East\Foundation\Router\ResultInterface as RouterResultInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Teknoo\East\Twig\Template\Engine;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Tests\East\Common\Behat\Extension\LoaderForTest;
use Teknoo\Tests\East\Common\Behat\Extension\ManagerForTest;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;
use Teknoo\Tests\East\Common\Behat\Object\MyObjectTimeStampable;
use Throwable;
use Twig\Environment;

use function array_key_exists;
use function bin2hex;
use function copy;
use function date_default_timezone_set;
use function error_reporting;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function in_array;
use function ini_set;
use function is_numeric;
use function json_decode;
use function json_encode;
use function opcache_invalidate;
use function parse_str;
use function random_bytes;
use function random_int;
use function realpath;
use function str_replace;
use function str_starts_with;
use function strlen;
use function unlink;

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
    private ?RecipeEndPoint $staticEndPoint = null;

    /**
     * @var RecipeEndPoint
     */
    private ?RecipeEndPoint $mediaEndPoint = null;

    public ?EngineInterface $templating = null;

    public ?Environment $twig = null;

    public ?Response $sfResponse = null;

    public ?ResponseInterface $response = null;

    public ?Throwable $error = null;

    public $createdObjects = [];

    public $updatedObjects = [];

    private ?User $user = null;

    private array $cookies = [];

    private ?MediaLoader $mediaLoader = null;

    public bool $noOverride = true;

    public function __construct()
    {
        date_default_timezone_set('UTC');

        error_reporting(E_ALL | E_STRICT);

        if (PHP_VERSION_ID < 80200) {
            ini_set('memory_limit', '196M');
        } else {
            ini_set('memory_limit', '128M');
        }
    }

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
     * @BeforeScenario
     */
    public function clean()
    {
        $this->user = null;
        $this->cookies = [];
        $this->noOverride = true;

        if (file_exists(__DIR__ . '/../var/cache/url_matching_routes.php')) {
            unlink(__DIR__ . '/../var/cache/url_matching_routes.php');
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(__DIR__ . '/../var/cache/url_matching_routes.php');
        }

        $routerRc = new ReflectionClass(Router::class);
        $routerRc->setStaticPropertyValue('cache', []);

        $this->getExtensionManagerForTest()->disabling();
    }

    private function getExtensionManagerForTest(): ManagerForTest
    {
        return ManagerForTest::run(new LoaderForTest());
    }

    /**
     * @Given I have DI With Symfony initialized
     */
    public function iHaveDiWithSymfonyInitialized(): void
    {
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
                return dirname(__DIR__).'/var/cache';
            }

            public function getLogDir(): string
            {
                return dirname(__DIR__).'/var/logs';
            }

            public function registerBundles(): iterable
            {
                yield new DIBridgeBundle();
                yield new EastFoundationBundle();
                yield new TeknooEastCommonBundle();
                yield new FrameworkBundle();
                yield new SecurityBundle();
                yield new SchebTwoFactorBundle();
            }

            protected function configureContainer(SfContainerBuilder $container, LoaderInterface $loader)
            {
                $loader->load(__DIR__.'/config/packages/*.yaml', 'glob');
                $loader->load(__DIR__.'/config/services.yaml');
                $container->setParameter('container.autowiring.strict_mode', true);
                $container->setParameter('container.dumper.inline_class_loader', true);
                $container->setParameter(
                    'teknoo.east.common.assets.no_overwrite',
                    $this->context->noOverride,
                );
            }

            protected function configureRoutes($routes): void
            {
                $thisDir = __DIR__;
                $rootDir = dirname(__DIR__, 2);
                $routes->import($rootDir . '/infrastructures/symfony/Resources/config/admin_*.yaml', 'glob')
                    ->prefix('/admin');
                $routes->import($thisDir . '/config/routes/*.yaml', 'glob');
                $routes->import($rootDir . '/infrastructures/symfony/Resources/config/r*.yaml', 'glob');
            }

            protected function getContainerClass(): string
            {
                $characters = 'abcdefghijklmnopqrstuvwxyz';
                $str = '';
                for ($i = 0; $i < 10; $i++) {
                    $str .= $characters[random_int(0, strlen($characters) - 1)];
                }

                return $str;
            }
        };
    }

    /**
     * @Given with css non minified files
     */
    public function withCssNonMinifiedFiles()
    {
        $file = realpath(__DIR__ . '/../support/build/css/main.min.css');
        if (false !== $file && file_exists($file)) {
            unlink($file);
        }

        $file = realpath(__DIR__ . '/../support/build/css/main.2.0.0.min.css');
        if (false !== $file && file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * @Given with css files already minified file into an unique file
     */
    public function withCssFilesAlreadyMinifiedFileIntoAnUniqueFile()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/css/prev.min.css');
        $fileMain = realpath(__DIR__ . '/../support/build/css/main.min.css');
        if (false !== $fileMain && file_exists($fileMain)) {
            unlink($fileMain);
        }

        copy($filePrev, __DIR__ . '/../support/build/css/main.min.css');

        $fileMain = realpath(__DIR__ . '/../support/build/css/main.2.0.0.min.css');
        if (false !== $fileMain && file_exists($fileMain)) {
            unlink($fileMain);
        }
    }

    /**
     * @Given with js non minified files
     */
    public function withJsNonMinifiedFiles()
    {
        $file = realpath(__DIR__ . '/../support/build/js/main.min.js');
        if (false !== $file && file_exists($file)) {
            unlink($file);
        }

        $file = realpath(__DIR__ . '/../support/build/js/main.2.0.0.min.js');
        if (false !== $file && file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * @Given with js files already minified file into an unique file
     */
    public function withJsFilesAlreadyMinifiedFileIntoAnUniqueFile()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/js/prev.min.js');
        $fileMain = realpath(__DIR__ . '/../support/build/js/main.min.js');
        if (false !== $fileMain && file_exists($fileMain)) {
            unlink($fileMain);
        }

        copy($filePrev, $fileMain);

        $fileMain = realpath(__DIR__ . '/../support/build/js/main.2.0.0.min.js');
        if (false !== $fileMain && file_exists($fileMain)) {
            unlink($fileMain);
        }
    }

    /**
     * @Given overriding of minified assets is enabled
     */
    public function overridingOfMinifiedAssetsIsEnabled()
    {
        $this->noOverride = false;
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
             * @param IdentifiedObjectInterface $object
             */
            public function persist($object)
            {
                if ($id = $object->getId()) {
                    $this->featureContext->updatedObjects[$id] = $object;
                } else {
                    $object->id = 'eastcommon' . bin2hex(random_bytes(23));
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
            private ?object $object;

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
                if (array_key_exists('deletedAt', $criteria)) {
                    unset($criteria['deletedAt']);
                }

                if (isset($criteria['or'][0]['active'])) {
                    unset($criteria['or']);
                }

                if (isset($criteria['slug']) && 'page-with-error' === $criteria['slug']) {
                    throw new Exception('Error', 404);
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

            public function getObject(): ?object
            {
                return $this->object;
            }
        };

        return $this->objectRepository;
    }

    /**
     * @Given a templating engine
     */
    public function aTemplatingEngine(): void
    {
        $this->templating = new class($this) implements EngineInterface {
            private $context;

            /**
             * @param \Teknoo\Tests\East\Common\Behat\FeatureContext $context
             */
            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            public function render(PromiseInterface $promise, $name, array $parameters = array()): EngineInterface
            {
                if ('404-error' === $name) {
                    $promise->fail(new Exception('Error 404'));

                    return $this;
                }

                Assert::assertEquals($this->context->templateToCall, $name);

                $keys = [];
                $values = [];

                $result = $this->context->buildResultObject(str_replace($keys, $values, $this->context->templateContent));
                $promise->success($result);

                return $this;
            }

            public function exists($name)
            {
            }

            public function supports($name)
            {
            }
        };

        $this->container->set(EngineInterface::class, $this->templating);
    }

    /**
     * @Given a Media Loader
     */
    public function aMediaLoader(): void
    {
        $this->mediaLoader = $this->container->get(MediaLoader::class);
    }

    /**
     * @Given an available image called :name
     */
    public function anAvailableImageCalled(string $name): void
    {
        $media = new class extends Media {
            /**
             * @inheritDoc
             */
            public function getResource()
            {
                $hf = \fopen('php://memory', 'rw');
                fwrite($hf, 'fooBar');
                fseek($hf, 0);

                return $hf;
            }
        };

        $this->getObjectRepository()->setObject(
            [
                'or' => [
                    ['id' => $name],
                    ['metadata.legacyId' => $name,]
                ]
            ],
            $media->setId($name)
                ->setName($name)
        );
    }

    /**
     * @Given a Endpoint able to serve resource from database.
     */
    public function aEndpointAbleToServeResourceFromDatabase(): void
    {
        $this->container->set(
            GetStreamFromMediaInterface::class,
            new class($this->container->get(StreamFactoryInterface::class)) implements GetStreamFromMediaInterface {
                protected StreamFactoryInterface $streamFactory;

                public function __construct(StreamFactoryInterface $streamFactory)
                {
                    $this->streamFactory = $streamFactory;
                }

                public function __invoke(
                    BaseMedia $media,
                    ManagerInterface $manager
                ): GetStreamFromMediaInterface {
                    $hf = fopen('php://memory', 'rw+');
                    fwrite($hf, 'fooBar');
                    fseek($hf, 0);

                    $stream = $this->streamFactory->createStreamFromResource($hf);

                    $manager->updateWorkPlan([
                        StreamInterface::class => $stream,
                    ]);

                    return $this;
                }
            }
        );

        $this->mediaEndPoint = new RecipeEndPoint(
            $this->container->get(RenderMediaEndPoint::class),
            $this->container
        );
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
                            if (!is_numeric($key)) {
                                $request = $request->withAttribute($key, $value);
                            }
                        }

                        foreach ($this->params[$route] as $key=>$value) {
                            $request = $request->withAttribute($key, $value);
                        }

                        $manager->updateWorkPlan([RouterResultInterface::class => $result]);

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
            case 'mediaEndPoint':
                $controller = $this->mediaEndPoint;
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
            public function errorInRequest(Throwable $throwable, bool $silently = false): ClientInterface
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
        parse_str($request->getUri()->getQuery(), $query);
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

        if (null !== $this->sfResponse) {
            foreach ($this->sfResponse->headers->getCookies() as $cookie) {
                $this->cookies[$cookie->getName()] = $cookie->getValue();
            }
        }
    }

    /**
     * @Then the response must be a css file
     */
    public function theResponseMustBeACssFile()
    {
        Assert::assertEquals(
            'text/css; charset=utf-8',
            $this->response->getHeader('Content-Type')[0]
        );
    }

    /**
     * @Then the response must be a js file
     */
    public function theResponseMustBeAJsFile()
    {
        Assert::assertEquals(
            'text/javascript; charset=utf-8',
            $this->response->getHeader('Content-Type')[0]
        );
    }

    /**
     * @Then the content must be the new minified css
     * @Then the content must be the new :extended minified css
     */
    public function theContentMustBeTheNewMinifiedCss(string $extended = '')
    {
        if (!empty($extended)) {
            $extended .= '.';
        }

        $fileExpected = realpath(__DIR__ . '/../support/build/css/' . $extended . 'expected.min.css');
        $fileMain = realpath(__DIR__ . '/../support/build/css/main.min.css');
        if (false === $fileMain) {
            $fileMain = realpath(__DIR__ . '/../support/build/css/main.2.0.0.min.css');
        }
        
        Assert::assertEquals(
            $expected = file_get_contents($fileExpected),
            file_get_contents($fileMain),
        );

        Assert::assertEquals(
            $expected,
            (string) $this->response->getBody(),
        );
    }

    /**
     * @Then the content must be the old minified css
     */
    public function theContentMustBeTheOldMinifiedCss()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/css/main.1.0.0.min.css.exp');

        Assert::assertEquals(
            file_get_contents($filePrev),
            (string) $this->response->getBody(),
        );
    }

    /**
     * @Then the content must be the existing minified css
     */
    public function theContentMustBeTheExistingMinifiedCss()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/css/prev.min.css');
        $fileMain = realpath(__DIR__ . '/../support/build/css/main.min.css');

        Assert::assertEquals(
            $expected = file_get_contents($filePrev),
            file_get_contents($fileMain),
        );

        Assert::assertEquals(
            $expected,
            (string) $this->response->getBody(),
        );
    }

    /**
     * @Then the content must be the new minified js
     * @Then the content must be the new :extended minified js
     */
    public function theContentMustBeTheNewMinifiedJs(string $extended = '')
    {
        if (!empty($extended)) {
            $extended .= '.';
        }

        $fileExpected = realpath(__DIR__ . '/../support/build/js/' . $extended . 'expected.min.js');
        $fileMain = realpath(__DIR__ . '/../support/build/js/main.min.js');
        if (false === $fileMain) {
            $fileMain = realpath(__DIR__ . '/../support/build/js/main.2.0.0.min.js');
        }

        Assert::assertEquals(
            $expected = file_get_contents($fileExpected),
            file_get_contents($fileMain),
        );

        Assert::assertEquals(
            $expected,
            (string) $this->response->getBody(),
        );
    }

    /**
     * @Then the content must be the old minified js
     */
    public function theContentMustBeTheOldMinifiedJs()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/js/main.1.0.0.min.js.exp');

        Assert::assertEquals(
            file_get_contents($filePrev),
            (string) $this->response->getBody(),
        );
    }

    /**
     * @Then the content must be the existing minified js
     */
    public function theContentMustBeTheExistingMinifiedJs()
    {
        $filePrev = realpath(__DIR__ . '/../support/build/js/prev.min.js');
        $fileMain = realpath(__DIR__ . '/../support/build/js/main.min.js');

        Assert::assertEquals(
            $expected = file_get_contents($filePrev),
            file_get_contents($fileMain),
        );

        Assert::assertEquals(
            $expected,
            (string) $this->response->getBody(),
        );
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
        Assert::assertInstanceOf(Throwable::class, $this->error);
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
                $apiTemplate = [
                    '@TeknooEastCommon/Api/delete.json.twig',

                ];

                if (in_array($name, $apiTemplate)) {
                    return json_encode(['success' => true]);
                }

                if (isset($parameters['totpAuth']) && ($totpAuth = $parameters['totpAuth']) instanceof TOTPAuth) {
                    return json_encode(
                        [
                            'topSecret' => $totpAuth->getTopSecret(),
                            'provider' => $totpAuth->getProvider(),
                            'enabled' => $totpAuth->isEnabled(),
                        ]
                    );
                }

                if (empty($parameters['objectInstance'])) {
                    return 'non-object-view';
                }

                //To avoid to manage templating view for crud
                $final = [];
                $ro = new ReflectionObject($object = $parameters['objectInstance']);
                foreach ($ro->getProperties() as $rp) {
                    if (in_array($rp->getName(), [
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
     * @Given with an extension in our application
     */
    public function withAnExtensionInOurApplication()
    {
        $this->getExtensionManagerForTest()->enabling();
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
            new Engine($this->twig)
        );

        $this->sfResponse = $this->symfonyKernel->handle($serverRequest);

        $psrFactory = new PsrHttpFactory(
            new ServerRequestFactory(),
            new StreamFactory(),
            new UploadedFileFactory(),
            new ResponseFactory()
        );

        $this->response = $psrFactory->createResponse($this->sfResponse);

        $this->symfonyKernel->terminate($serverRequest, $this->sfResponse);
    }

    /**
     * @When the client follows the redirection
     */
    public function theClientfollowsTheRedirection()
    {
        $url = current($this->response->getHeader('location'));
        if (!str_starts_with($url, 'https://')) {
            $url = 'https://foo.com' . $url;
        }
        $serverRequest = SfRequest::create(
            uri: $url,
            method: 'GET',
            cookies: $this->cookies,
        );

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

    private function getLocation(): string
    {
        return str_replace(
            'https://foo.com',
            '',
            current($this->response->getHeader('location')),
        );
    }

    /**
     * @Then It is redirect to :url
     */
    public function itIsRedirectTo($url)
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertEquals(302, $this->response->getStatusCode());
        $location = $this->getLocation();

        Assert::assertGreaterThan(0, preg_match("#$url#i", $location));
    }

    /**
     * @Then I should get in the form :body
     * @Then I should get in the response :body
     */
    public function iShouldGetInThe(string $body): void
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
        parse_str($body, $expectedBody);
        $serverRequest = SfRequest::create($url, 'POST', $expectedBody);

        $this->runSymfony($serverRequest);
    }

    /**
     * @When Symfony will receive the GET request :url
     */
    public function symfonyWillReceiveTheGetRequest($url)
    {
        $serverRequest = SfRequest::create($url, 'GET');

        $this->runSymfony($serverRequest);
    }


    /**
     * @When Symfony will receive the JSON request :url with :body
     */
    public function symfonyWillReceiveTheJsonRequestWith($url, $body)
    {
        $serverRequest = SfRequest::create(
            uri: $url,
            method: 'POST',
            server: [
                'CONTENT_TYPE' => 'application/json'
            ],
            content: $body,
        );

        $this->runSymfony($serverRequest);
    }

    /**
     * @When Symfony will receive a wrong 2FA Code
     */
    public function symfonyWillReceiveAWrongFaCode()
    {
        $serverRequest = SfRequest::create(
            uri: 'https://foo.com/user/2fa/check',
            method: 'POST',
            cookies: $this->cookies,
            parameters: [
                '_auth_code' => '000000',
            ],
        );

        $this->runSymfony($serverRequest);
    }

    /**
     * @When Symfony will receive a valid 2FA Code
     */
    public function symfonyWillReceiveAValidFaCode()
    {
        $serverRequest = SfRequest::create(
            uri: 'https://foo.com/user/2fa/check',
            method: 'POST',
            cookies: $this->cookies,
            parameters: [
                '_auth_code' => TOTP::createFromSecret(
                    secret: (string) $this->user?->getOneAuthData(TOTPAuth::class)->getTopSecret()
                )->now(),
            ],
        );

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
     * @Given a timestampable object with id :id and :properties
     */
    public function aTimestampableObjectOfTypeWithIdAnd($id, $properties)
    {
        $object = new MyObjectTimeStampable(
            id: $id,
            name: $properties['name'] ?? '',
            slug: $properties['slug'] ?? '',
            saved: $properties['saved'] ?? '',
        );

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
     * @Then a recovery session must be opened
     */
    public function aRecoverySessionMustBeOpened()
    {
        $container = $this->symfonyKernel->getContainer()->get(GetTokenStorageService::class);
        if (!$container->tokenStorage) {
            Assert::fail('The SecurityBundle is not registered in your application.');
        }

        Assert::assertNotEmpty($token = $container->tokenStorage->getToken());
        Assert::assertInstanceOf(UserWithRecoveryAccess::class, $token->getUser());
    }

    /**
     * @Then a session must be not opened
     */
    public function aSessionMustBeNotOpened()
    {
        $container = $this->symfonyKernel->getContainer()->get(GetTokenStorageService::class);
        if ($container->tokenStorage) {
            Assert::assertEmpty($token = $container->tokenStorage->getToken());
        }
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
            ->setRoles(['ROLE_USER'])
            ->setAuthData([$storedPassword]);

        $this->user = $object;
        $this->getObjectRepository()->setObject(['email' => 'admin@teknoo.software'], $object);
    }

    /**
     * @Given an 2FA authentication with a TOTP provider not enabled
     */
    public function anFaAuthenticationWithATotpProviderNotEnabled()
    {
        $this->user?->addAuthData(
            new TOTPAuth(
                provider: 'foo',
                topSecret: Base32::encodeUpperUnpadded(random_bytes(32)),
                period: 30,
                algorithm: 'sha1',
                digits: 6,
                enabled: false,
            )
        );
    }

    /**
     * @Given an 2FA authentication with a TOTP provider enabled
     */
    public function anFaAuthenticationWithATotpProviderEnabled()
    {
        $this->user?->addAuthData(
            new TOTPAuth(
                provider: 'foo',
                topSecret: 'bar',
                period: 30,
                algorithm: 'sha1',
                digits: 6,
                enabled: true,
            )
        );
    }

    /**
     * @When the date in object must be :date
     */
    public function theDateInObjectMustBe($date)
    {
        Assert::assertEquals(
            $this->getObjectRepository()->getObject()->updatedAt,
            new DateTimeImmutable($date)
        );
    }

    /**
     * @Then the date in object must be newer than :arg1
     */
    public function theDateInObjectMustBeNewerThan($date)
    {
        Assert::assertLessThan(
            $this->getObjectRepository()->getObject()->updatedAt,
            new DateTimeImmutable($date)
        );
    }

    /**
     * @Given set current datetime to :date
     */
    public function setCurrentDatetimeTo($date)
    {
        $this->symfonyKernel->boot();
        $this->symfonyKernel->getContainer()->get(DatesService::class)?->setCurrentDate(new DateTime($date));
    }

    /**
     * @When Symfony will receive a request to enable 2FA
     */
    public function symfonyWillReceiveARequestToEnableFa()
    {
        $serverRequest = SfRequest::create(
            uri: 'https://foo.com/user/common/2fa/enable',
            method: 'GET',
            cookies: $this->cookies,
        );

        $this->runSymfony($serverRequest);
    }

    /**
     * @Then the user have a disabled TOTPAuth configuration
     */
    public function theUserHaveADisabledTotpauthConfiguration()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertEquals(200, $this->response->getStatusCode());

        $json = json_decode((string) $this->response->getBody(), true);
        Assert::assertNotEmpty($json['topSecret'] ?? '');
        Assert::assertEquals('totp_custom', $json['provider'] ?? '');
        Assert::assertFalse($json['enabled'] ?? 'error');
    }

    /**
     * @When Symfony will receive a valid 2FA Confirmation
     */
    public function symfonyWillReceiveAValidFaConfirmation()
    {
        $serverRequest = SfRequest::create(
            uri: 'https://foo.com/user/common/2fa/validate',
            method: 'POST',
            cookies: $this->cookies,
            parameters: [
                'totp' => [
                    'code' => TOTP::createFromSecret(
                        secret: (string) $this->user?->getOneAuthData(TOTPAuth::class)->getTopSecret()
                    )->now()
                ],
            ],
        );

        $this->runSymfony($serverRequest);
    }

    /**
     * @Then the user have an enabled TOTPAuth configuration
     */
    public function theUserHaveAnEnabledTotpauthConfiguration()
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertEquals(200, $this->response->getStatusCode());

        $json = json_decode((string) $this->response->getBody(), true);
        Assert::assertNotEmpty($json['topSecret'] ?? '');
        Assert::assertEquals('totp_custom', $json['provider'] ?? '');
        Assert::assertTrue($json['enabled'] ?? 'error');
    }

    private function getMailerEvents(): MessageEvents
    {
        return $this->symfonyKernel->getContainer()->get('mailer.message_logger_listener')->getEvents();
    }

    /**
     * @Then no notification must be sent
     */
    public function noNotificationMustBeSent()
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(0),
        );
    }

    /**
     * @Then a notification must be sent
     */
    public function aNotificationMustBeSent()
    {
        Assert::assertThat(
            $this->getMailerEvents(),
            new EmailCount(1),
        );
    }

    /**
     * @When the user click on the link in the notification
     */
    public function theUserClickOnTheLinkInTheNotification()
    {
        $message = $this->getMailerEvents()->getMessages(null)[0];
        $context = $message->getContext();
        $actionUrl = $context['action_url'] ?? '';

        Assert::assertNotEmpty($actionUrl);

        $serverRequest = SfRequest::create($actionUrl, 'GET');

        $this->runSymfony($serverRequest);
    }
}

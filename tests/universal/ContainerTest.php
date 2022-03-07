<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website;

use PHPUnit\Framework\TestCase;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\CreateContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\DeleteContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\EditContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\ListContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderDynamicContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderMediaEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderStaticContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Website\DBSource\Repository\ContentRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\ItemRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\TypeRepositoryInterface;
use Teknoo\East\Website\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Website\DBSource\ManagerInterface as DbManagerInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Loader\TypeLoader;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Recipe\Cookbook\CreateContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\DeleteContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\EditContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\ListContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderDynamicContentEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Website\Recipe\Cookbook\RenderStaticContentEndPoint;
use Teknoo\East\Website\Recipe\Step\CreateObject;
use Teknoo\East\Website\Recipe\Step\DeleteObject;
use Teknoo\East\Website\Recipe\Step\ExtractOrder;
use Teknoo\East\Website\Recipe\Step\ExtractPage;
use Teknoo\East\Website\Recipe\Step\ExtractSlug;
use Teknoo\East\Website\Recipe\Step\LoadContent;
use Teknoo\East\Website\Recipe\Step\LoadListObjects;
use Teknoo\East\Website\Recipe\Step\LoadMedia;
use Teknoo\East\Website\Recipe\Step\LoadObject;
use Teknoo\East\Website\Recipe\Step\Render;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\RenderList;
use Teknoo\East\Website\Recipe\Step\SaveObject;
use Teknoo\East\Website\Recipe\Step\SearchFormHandling;
use Teknoo\East\Website\Recipe\Step\SendMedia;
use Teknoo\East\Website\Recipe\Step\SlugPreparation;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Writer\ItemWriter;
use Teknoo\East\Website\Writer\ContentWriter;
use Teknoo\East\Website\Writer\MediaWriter;
use Teknoo\East\Website\Writer\TypeWriter;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ContainerTest extends TestCase
{
    /**
     * @return Container
     * @throws \Exception
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        return $containerDefinition->build();
    }

    private function generateTestForLoader(string $className, string $repositoryInterface)
    {
        $container = $this->buildContainer();
        $repository = $this->createMock($repositoryInterface);

        $container->set($repositoryInterface, $repository);
        $loader = $container->get($className);

        self::assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testItemLoader()
    {
        $this->generateTestForLoader(ItemLoader::class, ItemRepositoryInterface::class);
    }

    public function testContentLoader()
    {
        $this->generateTestForLoader(ContentLoader::class, ContentRepositoryInterface::class);
    }

    public function testMediaLoader()
    {
        $this->generateTestForLoader(MediaLoader::class, MediaRepositoryInterface::class);
    }

    public function testTypeLoader()
    {
        $this->generateTestForLoader(TypeLoader::class, TypeRepositoryInterface::class);
    }

    public function testUserLoader()
    {
        $this->generateTestForLoader(UserLoader::class, UserRepositoryInterface::class);
    }

    private function generateTestForWriter(string $className)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($className);

        self::assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testItemWriter()
    {
        $this->generateTestForWriter(ItemWriter::class);
    }

    public function testContentWriter()
    {
        $this->generateTestForWriter(ContentWriter::class);
    }

    public function testMediaWriter()
    {
        $this->generateTestForWriter(MediaWriter::class);
    }

    public function testTypeWriter()
    {
        $this->generateTestForWriter(TypeWriter::class);
    }

    public function testUserWriter()
    {
        $this->generateTestForWriter(UserWriter::class);
    }

    private function generateTestForDelete(string $key)
    {
        $container = $this->buildContainer();
        $objectManager = $this->createMock(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($key);

        self::assertInstanceOf(
            DeletingService::class,
            $loader
        );
    }

    public function testItemDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.item');
    }

    public function testContentDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.content');
    }

    public function testMediaDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.media');
    }

    public function testTypeDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.type');
    }

    public function testUserDelete()
    {
        $this->generateTestForDelete('teknoo.east.website.deleting.user');
    }

    public function testMenuGenerator()
    {
        $container = $this->buildContainer();
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));
        $loader = $container->get(MenuGenerator::class);

        self::assertInstanceOf(
            MenuGenerator::class,
            $loader
        );
    }

    public function testMenuMiddleware()
    {
        $container = $this->buildContainer();
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));
        $loader = $container->get(MenuMiddleware::class);

        self::assertInstanceOf(
            MenuMiddleware::class,
            $loader
        );
    }

    public function testEastManagerMiddlewareInjection()
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        $container = $containerDefinition->build();

        $container->set(LoggerInterface::class, $this->createMock(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->set(ItemRepositoryInterface::class, $this->createMock(ItemRepositoryInterface::class));
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));

        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        self::assertInstanceOf(
            Manager::class,
            $manager1
        );

        self::assertInstanceOf(
            Manager::class,
            $manager2
        );

        self::assertSame($manager1, $manager2);
    }

    public function testCreateObject()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            CreateObject::class,
            $container->get(CreateObject::class)
        );
    }

    public function testDeleteObject()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            DeleteObject::class,
            $container->get(DeleteObject::class)
        );
    }

    public function testExtractOrder()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            ExtractOrder::class,
            $container->get(ExtractOrder::class)
        );
    }

    public function testExtractPage()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            ExtractPage::class,
            $container->get(ExtractPage::class)
        );
    }

    public function testExtractSlug()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            ExtractSlug::class,
            $container->get(ExtractSlug::class)
        );
    }

    public function testLoadContent()
    {
        $container = $this->buildContainer();
        $container->set(ContentRepositoryInterface::class, $this->createMock(ContentRepositoryInterface::class));

        self::assertInstanceOf(
            LoadContent::class,
            $container->get(LoadContent::class)
        );
    }

    public function testLoadListObjects()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            LoadListObjects::class,
            $container->get(LoadListObjects::class)
        );
    }

    public function testLoadMedia()
    {
        $container = $this->buildContainer();
        $container->set(MediaRepositoryInterface::class, $this->createMock(MediaRepositoryInterface::class));

        self::assertInstanceOf(
            LoadMedia::class,
            $container->get(LoadMedia::class)
        );
    }

    public function testLoadObject()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            LoadObject::class,
            $container->get(LoadObject::class)
        );
    }

    public function testRender()
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createMock(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            Render::class,
            $container->get(Render::class)
        );
    }

    public function testRenderError()
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createMock(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            RenderError::class,
            $container->get(RenderError::class)
        );
    }

    public function testRenderList()
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createMock(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            RenderList::class,
            $container->get(RenderList::class)
        );
    }

    public function testSaveObject()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            SaveObject::class,
            $container->get(SaveObject::class)
        );
    }

    public function testSendMedia()
    {
        $container = $this->buildContainer();
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            SendMedia::class,
            $container->get(SendMedia::class)
        );
    }

    public function testSlugPreparation()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            SlugPreparation::class,
            $container->get(SlugPreparation::class)
        );
    }

    public function testCreateContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createMock(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createMock(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createMock(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createMock(SaveObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            CreateContentEndPoint::class,
            $container->get(CreateContentEndPoint::class)
        );

        self::assertInstanceOf(
            CreateContentEndPointInterface::class,
            $container->get(CreateContentEndPointInterface::class)
        );
    }

    public function testCreateContentEndPointWithAccessControl()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createMock(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createMock(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createMock(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createMock(SaveObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createMock(ObjectAccessControlInterface::class));

        self::assertInstanceOf(
            CreateContentEndPoint::class,
            $container->get(CreateContentEndPoint::class)
        );

        self::assertInstanceOf(
            CreateContentEndPointInterface::class,
            $container->get(CreateContentEndPointInterface::class)
        );
    }

    public function testDeleteContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(DeleteObject::class, $this->createMock(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            DeleteContentEndPoint::class,
            $container->get(DeleteContentEndPoint::class)
        );

        self::assertInstanceOf(
            DeleteContentEndPointInterface::class,
            $container->get(DeleteContentEndPointInterface::class)
        );
    }

    public function testDeleteContentEndPointWithAccessControl()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(DeleteObject::class, $this->createMock(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createMock(ObjectAccessControlInterface::class));

        self::assertInstanceOf(
            DeleteContentEndPoint::class,
            $container->get(DeleteContentEndPoint::class)
        );

        self::assertInstanceOf(
            DeleteContentEndPointInterface::class,
            $container->get(DeleteContentEndPointInterface::class)
        );
    }

    public function testEditContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createMock(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createMock(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createMock(SaveObject::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            EditContentEndPoint::class,
            $container->get(EditContentEndPoint::class)
        );

        self::assertInstanceOf(
            EditContentEndPointInterface::class,
            $container->get(EditContentEndPointInterface::class)
        );
    }

    public function testEditContentEndPointWithAccessControl()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(FormHandlingInterface::class, $this->createMock(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createMock(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createMock(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createMock(SaveObject::class));
        $container->set(RenderFormInterface::class, $this->createMock(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createMock(ObjectAccessControlInterface::class));

        self::assertInstanceOf(
            EditContentEndPoint::class,
            $container->get(EditContentEndPoint::class)
        );

        self::assertInstanceOf(
            EditContentEndPointInterface::class,
            $container->get(EditContentEndPointInterface::class)
        );
    }

    public function testListContentEndPointWithoutFormLoader()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createMock(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createMock(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createMock(LoadListObjects::class));
        $container->set(RenderList::class, $this->createMock(RenderList::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            ListContentEndPoint::class,
            $container->get(ListContentEndPoint::class)
        );

        self::assertInstanceOf(
            ListContentEndPointInterface::class,
            $container->get(ListContentEndPointInterface::class)
        );
    }

    public function testListContentEndPointWithFormLoader()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createMock(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createMock(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createMock(LoadListObjects::class));
        $container->set(RenderList::class, $this->createMock(RenderList::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(SearchFormLoaderInterface::class, $this->createMock(SearchFormLoaderInterface::class));

        self::assertInstanceOf(
            ListContentEndPoint::class,
            $container->get(ListContentEndPoint::class)
        );

        self::assertInstanceOf(
            ListContentEndPointInterface::class,
            $container->get(ListContentEndPointInterface::class)
        );
    }

    public function testListContentEndPointWithAccessControl()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createMock(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createMock(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createMock(LoadListObjects::class));
        $container->set(RenderList::class, $this->createMock(RenderList::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(ListObjectsAccessControlInterface::class, $this->createMock(ListObjectsAccessControlInterface::class));

        self::assertInstanceOf(
            ListContentEndPoint::class,
            $container->get(ListContentEndPoint::class)
        );

        self::assertInstanceOf(
            ListContentEndPointInterface::class,
            $container->get(ListContentEndPointInterface::class)
        );
    }

    public function testRenderDynamicContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(ExtractSlug::class, $this->createMock(ExtractSlug::class));
        $container->set(LoadContent::class, $this->createMock(LoadContent::class));
        $container->set(Render::class, $this->createMock(Render::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            RenderDynamicContentEndPoint::class,
            $container->get(RenderDynamicContentEndPoint::class)
        );

        self::assertInstanceOf(
            RenderDynamicContentEndPointInterface::class,
            $container->get(RenderDynamicContentEndPointInterface::class)
        );
    }

    public function testRenderMediaEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadMedia::class, $this->createMock(LoadMedia::class));
        $container->set(GetStreamFromMediaInterface::class, $this->createMock(GetStreamFromMediaInterface::class));
        $container->set(SendMedia::class, $this->createMock(SendMedia::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            RenderMediaEndPoint::class,
            $container->get(RenderMediaEndPoint::class)
        );

        self::assertInstanceOf(
            RenderMediaEndPointInterface::class,
            $container->get(RenderMediaEndPointInterface::class)
        );
    }

    public function testRenderStaticContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(Render::class, $this->createMock(Render::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));

        self::assertInstanceOf(
            RenderStaticContentEndPoint::class,
            $container->get(RenderStaticContentEndPoint::class)
        );

        self::assertInstanceOf(
            RenderStaticContentEndPointInterface::class,
            $container->get(RenderStaticContentEndPointInterface::class)
        );
    }
}

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

namespace Teknoo\Tests\East\Common;

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface as DbManagerInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\MediaRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\CreateObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\EditObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\ListObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\MinifierEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\RenderMediaEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\RenderStaticContentEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Recipe\Cookbook\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\DeleteObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\EditObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\MinifierEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Common\Recipe\Cookbook\RenderStaticContentEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\ExtractSlug;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ComputePath;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadPersistedAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ReturnFile;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\LoadMedia;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SendMedia;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Common\Writer\MediaWriter;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\Foundation\Manager\Manager;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Router\RouterInterface;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

    public function testUserLoader()
    {
        $this->generateTestForLoader(UserLoader::class, UserRepositoryInterface::class);
    }

    public function testMediaLoader()
    {
        $this->generateTestForLoader(MediaLoader::class, MediaRepositoryInterface::class);
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

    public function testUserWriter()
    {
        $this->generateTestForWriter(UserWriter::class);
    }

    public function testMediaWriter()
    {
        $this->generateTestForWriter(MediaWriter::class);
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

    public function testUserDelete()
    {
        $this->generateTestForDelete('teknoo.east.common.deleting.user');
    }

    public function testMediaDelete()
    {
        $this->generateTestForDelete('teknoo.east.common.deleting.media');
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

    public function testSendMedia()
    {
        $container = $this->buildContainer();
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            SendMedia::class,
            $container->get(SendMedia::class)
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

    public function testLoadListObjects()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            LoadListObjects::class,
            $container->get(LoadListObjects::class)
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

    public function testJumpIf()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            JumpIf::class,
            $container->get(JumpIf::class)
        );
    }

    public function testStop()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            Stop::class,
            $container->get(Stop::class)
        );
    }

    public function testComputePathWithFinalLocation()
    {
        $container = $this->buildContainer();
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );

        self::assertInstanceOf(
            ComputePath::class,
            $container->get(ComputePath::class)
        );
    }

    public function testComputePath()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            ComputePath::class,
            $container->get(ComputePath::class)
        );
    }

    public function testLoadPersistedAsset()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            LoadPersistedAsset::class,
            $container->get(LoadPersistedAsset::class)
        );
    }

    public function testLoadSource()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            LoadSource::class,
            $container->get(LoadSource::class)
        );
    }

    public function testMinifyAssets()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            MinifyAssets::class,
            $container->get(MinifyAssets::class)
        );
    }

    public function testPersistAsset()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            PersistAsset::class,
            $container->get(PersistAsset::class)
        );
    }

    public function testReturnFile()
    {
        $container = $this->buildContainer();
        $container->set(StreamFactoryInterface::class, $this->createMock(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createMock(ResponseFactoryInterface::class));

        self::assertInstanceOf(
            ReturnFile::class,
            $container->get(ReturnFile::class)
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
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            CreateObjectEndPoint::class,
            $container->get(CreateObjectEndPoint::class)
        );

        self::assertInstanceOf(
            CreateObjectEndPointInterface::class,
            $container->get(CreateObjectEndPointInterface::class)
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
            CreateObjectEndPoint::class,
            $container->get(CreateObjectEndPoint::class)
        );

        self::assertInstanceOf(
            CreateObjectEndPointInterface::class,
            $container->get(CreateObjectEndPointInterface::class)
        );
    }

    public function testDeleteContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(DeleteObject::class, $this->createMock(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(Render::class, $this->createMock(Render::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            DeleteObjectEndPoint::class,
            $container->get(DeleteObjectEndPoint::class)
        );

        self::assertInstanceOf(
            DeleteObjectEndPointInterface::class,
            $container->get(DeleteObjectEndPointInterface::class)
        );
    }

    public function testDeleteContentEndPointWithAccessControl()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createMock(LoadObject::class));
        $container->set(DeleteObject::class, $this->createMock(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createMock(RedirectClientInterface::class));
        $container->set(Render::class, $this->createMock(Render::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createMock(ObjectAccessControlInterface::class));

        self::assertInstanceOf(
            DeleteObjectEndPoint::class,
            $container->get(DeleteObjectEndPoint::class)
        );

        self::assertInstanceOf(
            DeleteObjectEndPointInterface::class,
            $container->get(DeleteObjectEndPointInterface::class)
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
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            EditObjectEndPoint::class,
            $container->get(EditObjectEndPoint::class)
        );

        self::assertInstanceOf(
            EditObjectEndPointInterface::class,
            $container->get(EditObjectEndPointInterface::class)
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
            EditObjectEndPoint::class,
            $container->get(EditObjectEndPoint::class)
        );

        self::assertInstanceOf(
            EditObjectEndPointInterface::class,
            $container->get(EditObjectEndPointInterface::class)
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
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        self::assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
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
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        self::assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
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
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        self::assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
        );
    }

    public function testRenderStaticContentEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(Render::class, $this->createMock(Render::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            RenderStaticContentEndPoint::class,
            $container->get(RenderStaticContentEndPoint::class)
        );

        self::assertInstanceOf(
            RenderStaticContentEndPointInterface::class,
            $container->get(RenderStaticContentEndPointInterface::class)
        );
    }

    public function testMinifierEndPoint()
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createMock(OriginalRecipeInterface::class));
        $container->set(ComputePath::class, $this->createMock(ComputePath::class));
        $container->set(LoadPersistedAsset::class, $this->createMock(LoadPersistedAsset::class));
        $container->set(JumpIf::class, $this->createMock(JumpIf::class));
        $container->set(LoadSource::class, $this->createMock(LoadSource::class));
        $container->set(MinifyAssets::class, $this->createMock(MinifyAssets::class));
        $container->set(PersistAsset::class, $this->createMock(PersistAsset::class));
        $container->set(ReturnFile::class, $this->createMock(ReturnFile::class));
        $container->set(RenderError::class, $this->createMock(RenderError::class));
        $container->set('teknoo.east.common.cookbook.default_error_template', 'foo.template');

        self::assertInstanceOf(
            MinifierEndPoint::class,
            $container->get(MinifierEndPoint::class)
        );

        self::assertInstanceOf(
            MinifierEndPointInterface::class,
            $container->get(MinifierEndPointInterface::class)
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
}

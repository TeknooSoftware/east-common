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
use Teknoo\East\Common\Contracts\Recipe\Plan\CreateObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\EditObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\ListObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierCommandInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\PrepareRecoveryAccessEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\RenderMediaEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\RenderStaticContentEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\NotifyUserAboutRecoveryAccessInterface;
use Teknoo\East\Common\FrontAsset\Extensions\SourceLoader as SourceLoaderExtension;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Recipe\Plan\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Plan\DeleteObjectEndPoint;
use Teknoo\East\Common\Recipe\Plan\EditObjectEndPoint;
use Teknoo\East\Common\Recipe\Plan\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Plan\MinifierCommand;
use Teknoo\East\Common\Recipe\Plan\MinifierEndPoint;
use Teknoo\East\Common\Recipe\Plan\PrepareRecoveryAccessEndPoint;
use Teknoo\East\Common\Recipe\Plan\RenderMediaEndPoint;
use Teknoo\East\Common\Recipe\Plan\RenderStaticContentEndPoint;
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
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
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
use Teknoo\East\Common\Recipe\Step\User\FindUserByEmail;
use Teknoo\East\Common\Recipe\Step\User\PrepareRecoveryAccess;
use Teknoo\East\Common\Recipe\Step\User\RemoveRecoveryAccess;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Common\Writer\MediaWriter;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\Foundation\Extension\ManagerInterface as ExtensionManager;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerTest extends TestCase
{
    /**
     * @throws \Exception
     */
    protected function buildContainer(): Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        return $containerDefinition->build();
    }

    private function generateTestForLoader(string $className, string $repositoryInterface): void
    {
        $container = $this->buildContainer();
        $repository = $this->createStub($repositoryInterface);

        $container->set($repositoryInterface, $repository);
        $loader = $container->get($className);

        $this->assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testUserLoader(): void
    {
        $this->generateTestForLoader(UserLoader::class, UserRepositoryInterface::class);
    }

    public function testMediaLoader(): void
    {
        $this->generateTestForLoader(MediaLoader::class, MediaRepositoryInterface::class);
    }

    private function generateTestForWriter(string $className): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createStub(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($className);

        $this->assertInstanceOf(
            $className,
            $loader
        );
    }

    public function testUserWriter(): void
    {
        $this->generateTestForWriter(UserWriter::class);
    }

    public function testMediaWriter(): void
    {
        $this->generateTestForWriter(MediaWriter::class);
    }

    private function generateTestForDelete(string $key): void
    {
        $container = $this->buildContainer();
        $objectManager = $this->createStub(DbManagerInterface::class);

        $container->set(DbManagerInterface::class, $objectManager);
        $loader = $container->get($key);

        $this->assertInstanceOf(
            DeletingService::class,
            $loader
        );
    }

    public function testUserDelete(): void
    {
        $this->generateTestForDelete('teknoo.east.common.deleting.user');
    }

    public function testMediaDelete(): void
    {
        $this->generateTestForDelete('teknoo.east.common.deleting.media');
    }

    public function testLoadMedia(): void
    {
        $container = $this->buildContainer();
        $container->set(MediaRepositoryInterface::class, $this->createStub(MediaRepositoryInterface::class));

        $this->assertInstanceOf(
            LoadMedia::class,
            $container->get(LoadMedia::class)
        );
    }

    public function testSendMedia(): void
    {
        $container = $this->buildContainer();
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            SendMedia::class,
            $container->get(SendMedia::class)
        );
    }

    public function testEastManagerMiddlewareInjection(): void
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../vendor/teknoo/east-foundation/src/di.php');
        $containerDefinition->addDefinitions(__DIR__ . '/../../src/di.php');

        $container = $containerDefinition->build();

        $container->set(LoggerInterface::class, $this->createStub(LoggerInterface::class));
        $container->set(RouterInterface::class, $this->createStub(RouterInterface::class));

        $manager1 = $container->get(Manager::class);
        $manager2 = $container->get(ManagerInterface::class);

        $this->assertInstanceOf(
            Manager::class,
            $manager1
        );

        $this->assertInstanceOf(
            Manager::class,
            $manager2
        );

        $this->assertSame($manager1, $manager2);
    }

    public function testCreateObject(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            CreateObject::class,
            $container->get(CreateObject::class)
        );
    }

    public function testDeleteObject(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            DeleteObject::class,
            $container->get(DeleteObject::class)
        );
    }

    public function testExtractOrder(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ExtractOrder::class,
            $container->get(ExtractOrder::class)
        );
    }

    public function testExtractPage(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ExtractPage::class,
            $container->get(ExtractPage::class)
        );
    }

    public function testExtractSlug(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ExtractSlug::class,
            $container->get(ExtractSlug::class)
        );
    }

    public function testLoadListObjects(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            LoadListObjects::class,
            $container->get(LoadListObjects::class)
        );
    }

    public function testLoadObject(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            LoadObject::class,
            $container->get(LoadObject::class)
        );
    }

    public function testRender(): void
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createStub(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            Render::class,
            $container->get(Render::class)
        );
    }

    public function testRenderError(): void
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createStub(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            RenderError::class,
            $container->get(RenderError::class)
        );
    }

    public function testRenderList(): void
    {
        $container = $this->buildContainer();
        $container->set(EngineInterface::class, $this->createStub(EngineInterface::class));
        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            RenderList::class,
            $container->get(RenderList::class)
        );
    }

    public function testSaveObject(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            SaveObject::class,
            $container->get(SaveObject::class)
        );
    }

    public function testJumpIf(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            JumpIf::class,
            $container->get(JumpIf::class)
        );
    }

    public function testJumpIfNot(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            JumpIfNot::class,
            $container->get(JumpIfNot::class)
        );
    }

    public function testStop(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            Stop::class,
            $container->get(Stop::class)
        );
    }

    public function testComputePathWithFinalLocation(): void
    {
        $container = $this->buildContainer();
        $container->set(
            'teknoo.east.common.assets.final_location',
            'foo',
        );

        $this->assertInstanceOf(
            ComputePath::class,
            $container->get(ComputePath::class)
        );
    }

    public function testComputePath(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            ComputePath::class,
            $container->get(ComputePath::class)
        );
    }

    public function testLoadPersistedAsset(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            LoadPersistedAsset::class,
            $container->get(LoadPersistedAsset::class)
        );
    }

    public function testLoadSource(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            LoadSource::class,
            $container->get(LoadSource::class)
        );
    }

    public function testMinifyAssets(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            MinifyAssets::class,
            $container->get(MinifyAssets::class)
        );
    }

    public function testPersistAsset(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            PersistAsset::class,
            $container->get(PersistAsset::class)
        );
    }

    public function testReturnFile(): void
    {
        $container = $this->buildContainer();
        $container->set(StreamFactoryInterface::class, $this->createStub(StreamFactoryInterface::class));
        $container->set(ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class));

        $this->assertInstanceOf(
            ReturnFile::class,
            $container->get(ReturnFile::class)
        );
    }

    public function testFindUserByEmail(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            FindUserByEmail::class,
            $container->get(FindUserByEmail::class)
        );
    }


    public function testPrepareRecoveryAccess(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            PrepareRecoveryAccess::class,
            $container->get(PrepareRecoveryAccess::class)
        );
    }


    public function testRemoveRecoveryAccess(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            RemoveRecoveryAccess::class,
            $container->get(RemoveRecoveryAccess::class)
        );
    }


    public function testSlugPreparation(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(
            SlugPreparation::class,
            $container->get(SlugPreparation::class)
        );
    }

    public function testCreateObjectEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createStub(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createStub(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createStub(SaveObject::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            CreateObjectEndPoint::class,
            $container->get(CreateObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            CreateObjectEndPointInterface::class,
            $container->get(CreateObjectEndPointInterface::class)
        );
    }

    public function testCreateObjectEndPointWithAccessControl(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createStub(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createStub(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createStub(SaveObject::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createStub(ObjectAccessControlInterface::class));

        $this->assertInstanceOf(
            CreateObjectEndPoint::class,
            $container->get(CreateObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            CreateObjectEndPointInterface::class,
            $container->get(CreateObjectEndPointInterface::class)
        );
    }

    public function testDeleteObjectEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createStub(LoadObject::class));
        $container->set(DeleteObject::class, $this->createStub(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(Render::class, $this->createStub(Render::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            DeleteObjectEndPoint::class,
            $container->get(DeleteObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            DeleteObjectEndPointInterface::class,
            $container->get(DeleteObjectEndPointInterface::class)
        );
    }

    public function testDeleteObjectEndPointWithAccessControl(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createStub(LoadObject::class));
        $container->set(DeleteObject::class, $this->createStub(DeleteObject::class));
        $container->set(RedirectClientInterface::class, $this->createStub(RedirectClientInterface::class));
        $container->set(Render::class, $this->createStub(Render::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createStub(ObjectAccessControlInterface::class));

        $this->assertInstanceOf(
            DeleteObjectEndPoint::class,
            $container->get(DeleteObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            DeleteObjectEndPointInterface::class,
            $container->get(DeleteObjectEndPointInterface::class)
        );
    }

    public function testEditObjectEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createStub(LoadObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createStub(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createStub(SaveObject::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            EditObjectEndPoint::class,
            $container->get(EditObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            EditObjectEndPointInterface::class,
            $container->get(EditObjectEndPointInterface::class)
        );
    }

    public function testEditObjectEndPointWithAccessControl(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadObject::class, $this->createStub(LoadObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(SlugPreparation::class, $this->createStub(SlugPreparation::class));
        $container->set(SaveObject::class, $this->createStub(SaveObject::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set(ObjectAccessControlInterface::class, $this->createStub(ObjectAccessControlInterface::class));

        $this->assertInstanceOf(
            EditObjectEndPoint::class,
            $container->get(EditObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            EditObjectEndPointInterface::class,
            $container->get(EditObjectEndPointInterface::class)
        );
    }

    public function testListObjectEndPointWithoutFormLoader(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createStub(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createStub(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createStub(LoadListObjects::class));
        $container->set(RenderList::class, $this->createStub(RenderList::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
        );
    }

    public function testListObjectEndPointWithFormLoader(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createStub(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createStub(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createStub(LoadListObjects::class));
        $container->set(RenderList::class, $this->createStub(RenderList::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set(SearchFormLoaderInterface::class, $this->createStub(SearchFormLoaderInterface::class));

        $this->assertInstanceOf(
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
        );
    }

    public function testListObjectEndPointWithAccessControl(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(ExtractPage::class, $this->createStub(ExtractPage::class));
        $container->set(ExtractOrder::class, $this->createStub(ExtractOrder::class));
        $container->set(LoadListObjects::class, $this->createStub(LoadListObjects::class));
        $container->set(RenderList::class, $this->createStub(RenderList::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set(ListObjectsAccessControlInterface::class, $this->createStub(ListObjectsAccessControlInterface::class));

        $this->assertInstanceOf(
            ListObjectEndPoint::class,
            $container->get(ListObjectEndPoint::class)
        );

        $this->assertInstanceOf(
            ListObjectEndPointInterface::class,
            $container->get(ListObjectEndPointInterface::class)
        );
    }

    public function testRenderStaticContentEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(Render::class, $this->createStub(Render::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            RenderStaticContentEndPoint::class,
            $container->get(RenderStaticContentEndPoint::class)
        );

        $this->assertInstanceOf(
            RenderStaticContentEndPointInterface::class,
            $container->get(RenderStaticContentEndPointInterface::class)
        );
    }

    public function testMinifierEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(ComputePath::class, $this->createStub(ComputePath::class));
        $container->set(LoadPersistedAsset::class, $this->createStub(LoadPersistedAsset::class));
        $container->set(JumpIf::class, $this->createStub(JumpIf::class));
        $container->set(LoadSource::class, $this->createStub(LoadSource::class));
        $container->set(MinifyAssets::class, $this->createStub(MinifyAssets::class));
        $container->set(PersistAsset::class, $this->createStub(PersistAsset::class));
        $container->set(ReturnFile::class, $this->createStub(ReturnFile::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            MinifierEndPoint::class,
            $container->get(MinifierEndPoint::class)
        );

        $this->assertInstanceOf(
            MinifierEndPointInterface::class,
            $container->get(MinifierEndPointInterface::class)
        );
    }

    public function testMinifierCommand(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadSource::class, $this->createStub(LoadSource::class));
        $container->set(MinifyAssets::class, $this->createStub(MinifyAssets::class));
        $container->set(PersistAsset::class, $this->createStub(PersistAsset::class));

        $this->assertInstanceOf(
            MinifierCommand::class,
            $container->get(MinifierCommand::class)
        );

        $this->assertInstanceOf(
            MinifierCommandInterface::class,
            $container->get(MinifierCommandInterface::class)
        );
    }

    public function testRenderMediaEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(LoadMedia::class, $this->createStub(LoadMedia::class));
        $container->set(GetStreamFromMediaInterface::class, $this->createStub(GetStreamFromMediaInterface::class));
        $container->set(SendMedia::class, $this->createStub(SendMedia::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));

        $this->assertInstanceOf(
            RenderMediaEndPoint::class,
            $container->get(RenderMediaEndPoint::class)
        );

        $this->assertInstanceOf(
            RenderMediaEndPointInterface::class,
            $container->get(RenderMediaEndPointInterface::class)
        );
    }

    public function testPrepareRecoveryAccessEndPoint(): void
    {
        $container = $this->buildContainer();
        $container->set(OriginalRecipeInterface::class, $this->createStub(OriginalRecipeInterface::class));
        $container->set(CreateObject::class, $this->createStub(CreateObject::class));
        $container->set(FormHandlingInterface::class, $this->createStub(FormHandlingInterface::class));
        $container->set(FormProcessingInterface::class, $this->createStub(FormProcessingInterface::class));
        $container->set(FindUserByEmail::class, $this->createStub(FindUserByEmail::class));
        $container->set(JumpIf::class, $this->createStub(JumpIf::class));
        $container->set(RemoveRecoveryAccess::class, $this->createStub(RemoveRecoveryAccess::class));
        $container->set(PrepareRecoveryAccess::class, $this->createStub(PrepareRecoveryAccess::class));
        $container->set(SaveObject::class, $this->createStub(SaveObject::class));
        $container->set(NotifyUserAboutRecoveryAccessInterface::class, $this->createStub(NotifyUserAboutRecoveryAccessInterface::class));
        $container->set(Render::class, $this->createStub(Render::class));
        $container->set(Stop::class, $this->createStub(Stop::class));
        $container->set(RenderFormInterface::class, $this->createStub(RenderFormInterface::class));
        $container->set(RenderError::class, $this->createStub(RenderError::class));
        $container->set('teknoo.east.common.plan.default_error_template', 'foo.template');

        $this->assertInstanceOf(
            PrepareRecoveryAccessEndPoint::class,
            $container->get(PrepareRecoveryAccessEndPoint::class)
        );

        $this->assertInstanceOf(
            PrepareRecoveryAccessEndPointInterface::class,
            $container->get(PrepareRecoveryAccessEndPointInterface::class)
        );
    }

    public function testSourceLoaderExtension(): void
    {
        $container = $this->buildContainer();
        $container->set(ExtensionManager::class, $this->createStub(ExtensionManager::class));

        $this->assertInstanceOf(
            SourceLoaderExtension::class,
            $container->get(SourceLoaderExtension::class)
        );
    }

    public function testGetDefaultErrorTemplate(): void
    {
        $container = $this->buildContainer();
        $container->set(
            'teknoo.east.common.plan.default_error_template',
            'foo'
        );

        $template = $container->get('teknoo.east.common.get_default_error_template');
        $this->assertInstanceOf(
            \Stringable::class,
            $template,
        );

        $this->assertEquals(
            'foo',
            (string) $template,
        );
    }
}

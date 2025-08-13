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

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\ODM;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\FilterCollection;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\Doctrine\Contracts\DBSource\ConfigurationHelperInterface;
use Teknoo\East\Common\Doctrine\DBSource\ODM\ConfigurationHelper;
use Teknoo\East\Common\Doctrine\Filter\ODM\SoftDeletableFilter;
use TypeError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ConfigurationHelper::class)]
class ConfigurationHelperTest extends TestCase
{
    private function buildConfigurationHelper(): ConfigurationHelper
    {
        return new ConfigurationHelper();
    }

    private function buildUsableHelper(): ConfigurationHelper
    {
        $dm = new class () extends DocumentManager {
            public function __construct(
                private ?Configuration $configuration = null,
                private ?FilterCollection $filterCollection = null,
            ) {
            }

            public function getConfiguration(): Configuration
            {
                return $this->configuration ??= new Configuration();
            }

            public function getFilterCollection(): FilterCollection
            {
                return $this->filterCollection ??= new FilterCollection($this);
            }
        };

        return $this->buildConfigurationHelper()->setManager(
            $this->createMock(ManagerInterface::class),
            $dm,
        );
    }

    public function testSetManagerWithNonDocumentManager(): void
    {
        $this->expectException(TypeError::class);
        $this->buildConfigurationHelper()->setManager(
            $this->createMock(ManagerInterface::class),
            $this->createMock(ObjectManager::class),
        );
    }

    public function testSetManagerWithDocumentManager(): void
    {
        $this->assertInstanceOf(
            ConfigurationHelperInterface::class,
            $this->buildUsableHelper(),
        );
    }

    public function testRegisterFilterWithNonInitializedHelper(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildConfigurationHelper()->registerFilter('foo', ['bar' => 'foo']);
    }

    public function testRegisterFilter(): void
    {
        $helper = $this->buildUsableHelper();
        $this->assertInstanceOf(
            ConfigurationHelperInterface::class,
            $helper->registerFilter(SoftDeletableFilter::class, [])
        );
    }

    public function testEnableFilterWithNonInitializedHelper(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildConfigurationHelper()->registerFilter(SoftDeletableFilter::class, [])->enableFilter('foo');
    }

    public function testEnableFilter(): void
    {
        $helper = $this->buildUsableHelper();
        $this->assertInstanceOf(
            ConfigurationHelperInterface::class,
            $helper->registerFilter(SoftDeletableFilter::class, [])->enableFilter(SoftDeletableFilter::class)
        );
    }

    public function testDisableFilterWithNonInitializedHelper(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildConfigurationHelper()->registerFilter(SoftDeletableFilter::class, [])->disableFilter('foo');
    }

    public function testDisableFilter(): void
    {
        $helper = $this->buildUsableHelper();
        $this->assertInstanceOf(
            ConfigurationHelperInterface::class,
            $helper->registerFilter(SoftDeletableFilter::class, [])->disableFilter(SoftDeletableFilter::class)
        );
    }
}

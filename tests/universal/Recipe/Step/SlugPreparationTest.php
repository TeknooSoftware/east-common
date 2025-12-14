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

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SlugPreparation::class)]
class SlugPreparationTest extends TestCase
{
    private (FindSlugService&Stub)|(FindSlugService&MockObject)|null $findSlugService = null;

    private function getFindSlugService(bool $stub = false): (FindSlugService&Stub)|(FindSlugService&MockObject)
    {
        if (!$this->findSlugService instanceof FindSlugService) {
            if ($stub) {
                $this->findSlugService = $this->createStub(FindSlugService::class);
            } else {
                $this->findSlugService = $this->createMock(FindSlugService::class);
            }
        }

        return $this->findSlugService;
    }

    public function buildStep(): SlugPreparation
    {
        return new SlugPreparation(
            $this->getFindSlugService(true)
        );
    }

    public function testInvokeBadLoader(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createStub(IdentifiedObjectInterface::class),
            'foo'
        );
    }

    public function testInvokeBadObject(): void
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createStub(LoaderInterface::class),
            new \stdClass(),
            'foo'
        );
    }

    public function testInvokeBadSlugField(): void
    {
        $this->expectException(\TypeError::class);

        $object = new class () implements IdentifiedObjectInterface, SluggableInterface {
            public function getId(): string
            {
                return 123;
            }

            public function prepareSlugNear(
                LoaderInterface $loader,
                FindSlugService $findSlugService,
                string $slugField
            ): SluggableInterface {
                return $this;
            }

            public function setSlug(string $slug): SluggableInterface
            {
                return $this;
            }
        };

        $this->buildStep()(
            $this->createStub(LoaderInterface::class),
            $object,
            new \stdClass()
        );
    }

    public function testInvokeWithNonSluggable(): void
    {
        $object = new class () implements IdentifiedObjectInterface, SluggableInterface {
            public function getId(): string
            {
                return 123;
            }

            public function prepareSlugNear(
                LoaderInterface $loader,
                FindSlugService $findSlugService,
                string $slugField
            ): SluggableInterface {
                return $this;
            }

            public function setSlug(string $slug): SluggableInterface
            {
                return $this;
            }
        };

        $this->assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createStub(LoaderInterface::class),
                $object,
                'slug'
            )
        );
    }

    public function testInvokeWithNonSlugField(): void
    {
        $object = new class () implements IdentifiedObjectInterface, SluggableInterface {
            public function getId(): string
            {
                return 123;
            }

            public function prepareSlugNear(
                LoaderInterface $loader,
                FindSlugService $findSlugService,
                string $slugField
            ): SluggableInterface {
                return $this;
            }

            public function setSlug(string $slug): SluggableInterface
            {
                return $this;
            }
        };

        $this->assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createStub(LoaderInterface::class),
                $object,
                null
            )
        );
    }

    public function testInvokeWithSlugField(): void
    {
        $object = new class () implements IdentifiedObjectInterface, SluggableInterface {
            public function getId(): string
            {
                return 123;
            }

            public function prepareSlugNear(
                LoaderInterface $loader,
                FindSlugService $findSlugService,
                string $slugField
            ): SluggableInterface {
                return $this;
            }

            public function setSlug(string $slug): SluggableInterface
            {
                return $this;
            }
        };

        $this->assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createStub(LoaderInterface::class),
                $object,
                'slug'
            )
        );
    }
}

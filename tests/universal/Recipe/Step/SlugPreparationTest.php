<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Recipe\Step\SlugPreparation
 */
class SlugPreparationTest extends TestCase
{
    private ?FindSlugService $findSlugService = null;

    /**
     * @return FindSlugService|MockObject
     */
    private function getFindSlugService(): FindSlugService
    {
        if (!$this->findSlugService instanceof FindSlugService) {
            $this->findSlugService = $this->createMock(FindSlugService::class);
        }

        return $this->findSlugService;
    }

    public function buildStep(): SlugPreparation
    {
        return new SlugPreparation($this->getFindSlugService());
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(IdentifiedObjectInterface::class),
            'foo'
        );
    }

    public function testInvokeBadObject()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            'foo'
        );
    }

    public function testInvokeBadSlugField()
    {
        $this->expectException(\TypeError::class);

        $object = new class implements IdentifiedObjectInterface, SluggableInterface {
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
            $this->createMock(LoaderInterface::class),
            $object,
            new \stdClass()
        );
    }

    public function testInvokeWithNonSluggable()
    {
        $object = new class implements IdentifiedObjectInterface, SluggableInterface {
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

        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $object,
                'slug'
            )
        );
    }

    public function testInvokeWithNonSlugField()
    {
        $object = new class implements IdentifiedObjectInterface, SluggableInterface {
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

        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $object,
                null
            )
        );
    }

    public function testInvokeWithSlugField()
    {
        $object = new class implements IdentifiedObjectInterface, SluggableInterface {
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

        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $object,
                'slug'
            )
        );
    }
}

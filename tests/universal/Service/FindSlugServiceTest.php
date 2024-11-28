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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\SluggableInterface;
use Teknoo\East\Common\Query\FindBySlugQuery;
use Teknoo\East\Common\Service\FindSlugService;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FindSlugService::class)]
class FindSlugServiceTest extends TestCase
{
    public function buildService(): FindSlugService
    {
        return new FindSlugService();
    }

    public function testProcessSlugAvailable()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $sluggable = $this->createMock(SluggableInterface::class);

        $loader->expects($this->once())
            ->method('fetch')
            ->with(new FindBySlugQuery('slugField', 'foo-bar'))
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader) {
                    $promise->fail(new \DomainException('Not Found'));

                    return $loader;
                }
            );

        $sluggable->expects($this->once())
            ->method('setSlug')
            ->with('foo-bar')
            ->willReturnSelf();

        self::assertInstanceOf(
            FindSlugService::class,
            $this->buildService()->process(
                $loader,
                'slugField',
                $sluggable,
                ['Foo', 'bAr']
            )
        );
    }

    public function testProcessSlugAvailableWithObject()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $sluggable = new class implements SluggableInterface, IdentifiedObjectInterface {
            public function getId(): string
            {
                return 'foo';
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

        $loader->expects($this->once())
            ->method('fetch')
            ->with(new FindBySlugQuery('slugField', 'foo-bar', false, $sluggable))
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader) {
                    $promise->fail(new \DomainException('Not Found'));

                    return $loader;
                }
            );

        self::assertInstanceOf(
            FindSlugService::class,
            $this->buildService()->process(
                $loader,
                'slugField',
                $sluggable,
                ['Foo', 'bAr']
            )
        );
    }

    public function testProcessSlugFirstAndSecondNotAvailable()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $sluggable = $this->createMock(SluggableInterface::class);

        $counter=0;
        $loader->expects($this->exactly(3))
            ->method('fetch')
            ->with(
                $this->callback(
                    function ($value): bool {
                        if ($value == (new FindBySlugQuery('slugField', 'foo-bar'))) {
                            return true;
                        }
                        if ($value == (new FindBySlugQuery('slugField', 'foo-bar-2'))) {
                            return true;
                        }
                        if ($value == (new FindBySlugQuery('slugField', 'foo-bar-3'))) {
                            return true;
                        }
                        return false;
                    }
                )
            )
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader, &$counter) {
                    if ($counter++ < 2) {
                        $promise->success($this->createMock(IdentifiedObjectInterface::class));
                    } else {
                        $promise->fail(new \DomainException('Not Found'));
                    }

                    return $loader;
                }
            );

        $sluggable->expects($this->once())
            ->method('setSlug')
            ->with('foo-bar-3')
            ->willReturnSelf();

        self::assertInstanceOf(
            FindSlugService::class,
            $this->buildService()->process(
                $loader,
                'slugField',
                $sluggable,
                ['Foo', 'bAr']
            )
        );
    }
}

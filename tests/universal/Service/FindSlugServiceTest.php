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

namespace Teknoo\Tests\East\Website\Service;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Object\SluggableInterface;
use Teknoo\East\Website\Query\FindBySlugQuery;
use Teknoo\East\Website\Service\FindSlugService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Service\FindSlugService
 */
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

        $loader->expects(self::once())
            ->method('query')
            ->with(new FindBySlugQuery('slugField', 'foo-bar'))
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader) {
                    $promise->fail(new \DomainException('Not Found'));

                    return $loader;
                }
            );

        $sluggable->expects(self::once())
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
        $sluggable = new class implements SluggableInterface, ObjectInterface {
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

        $loader->expects(self::once())
            ->method('query')
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
        $loader->expects(self::exactly(3))
            ->method('query')
            ->withConsecutive(
                [new FindBySlugQuery('slugField', 'foo-bar')],
                [new FindBySlugQuery('slugField', 'foo-bar-2')],
                [new FindBySlugQuery('slugField', 'foo-bar-3')],
            )
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) use ($loader, &$counter) {
                    if ($counter++ < 2) {
                        $promise->success($this->createMock(ObjectInterface::class));
                    } else {
                        $promise->fail(new \DomainException('Not Found'));
                    }

                    return $loader;
                }
            );

        $sluggable->expects(self::once())
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

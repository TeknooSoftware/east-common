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

namespace Teknoo\Tests\East\Common\Object\Collection;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Object\Collection\LazyLoadableCollection;
use Teknoo\Recipe\Promise\Promise;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LazyLoadableCollection::class)]
class LazyLoadableCollectionTest extends TestCase
{
    public function testFetchCollectionSuccess(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('query')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Query\QueryCollectionInterface $query, Promise $promise) use ($loader): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success([]);

                    return $loader;
                }
            );

        $this->assertInstanceOf(
            LazyLoadableCollection::class,
            new LazyLoadableCollection(
                loader: $loader,
                query: $this->createMock(QueryCollectionInterface::class),
            )->fetchCollection()
        );
    }

    public function testFetchCollectionFail(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('query')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Query\QueryCollectionInterface $query, Promise $promise) use ($loader): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->fail(new Exception('error'));

                    return $loader;
                }
            );

        $this->expectException(Exception::class);
        $this->assertInstanceOf(
            LazyLoadableCollection::class,
            new LazyLoadableCollection(
                loader: $loader,
                query: $this->createMock(QueryCollectionInterface::class),
            )->fetchCollection()
        );
    }

    public function testGetIterator(): void
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader
            ->method('query')
            ->willReturnCallback(
                function (\Teknoo\East\Common\Contracts\Query\QueryCollectionInterface $query, Promise $promise) use ($loader): \PHPUnit\Framework\MockObject\MockObject {
                    $promise->success([
                        $this->createMock(ObjectInterface::class),
                        $this->createMock(ObjectInterface::class),
                    ]);

                    return $loader;
                }
            );

        $collection = new LazyLoadableCollection(
            loader: $loader,
            query: $this->createMock(QueryCollectionInterface::class),
        );

        $i = 0;
        foreach ($collection as $item) {
            $this->assertInstanceOf(
                ObjectInterface::class,
                $item,
            );
            ++$i;
        }

        $this->assertEquals(2, $i);
    }

    public function testGetIteratorWithWrongLoading(): void
    {
        $collection = new LazyLoadableCollection(
            loader: $this->createMock(LoaderInterface::class),
            query: $this->createMock(QueryCollectionInterface::class),
        );

        $this->expectException(RuntimeException::class);
        foreach ($collection as $item) {
            self::fail('Collection could not be fetched');
        }
    }
}

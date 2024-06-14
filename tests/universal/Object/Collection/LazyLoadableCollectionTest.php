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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(LazyLoadableCollection::class)]
class LazyLoadableCollectionTest extends TestCase
{
    public function testFetchCollectionSuccess()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
            ->method('query')
            ->willReturnCallback(
                function ($query, Promise $promise) use ($loader) {
                    $promise->success([]);

                    return $loader;
                }
            );

        self::assertInstanceOf(
            LazyLoadableCollection::class,
            (new LazyLoadableCollection(
                loader: $loader,
                query: $this->createMock(QueryCollectionInterface::class),
            ))->fetchCollection()
        );
    }

    public function testFetchCollectionFail()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
            ->method('query')
            ->willReturnCallback(
                function ($query, Promise $promise) use ($loader) {
                    $promise->fail(new Exception('error'));

                    return $loader;
                }
            );

        $this->expectException(Exception::class);
        self::assertInstanceOf(
            LazyLoadableCollection::class,
            (new LazyLoadableCollection(
                loader: $loader,
                query: $this->createMock(QueryCollectionInterface::class),
            ))->fetchCollection()
        );
    }

    public function testGetIterator()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects($this->any())
            ->method('query')
            ->willReturnCallback(
                function ($query, Promise $promise) use ($loader) {
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
            self::assertInstanceOf(
                ObjectInterface::class,
                $item,
            );
            $i++;
        }

        self::assertEquals(2, $i);
    }

    public function testGetIteratorWithWrongLoading()
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

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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object\Collection;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Query\QueryCollectionInterface;
use Teknoo\East\Common\Object\Collection\LazyLoadableCollection;
use Teknoo\Recipe\Promise\Promise;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Common\Object\Collection\LazyLoadableCollection
 */
class LazyLoadableCollectionTest extends TestCase
{
    public function testFetchCollectionSuccess()
    {
        $loader = $this->createMock(LoaderInterface::class);
        $loader->expects(self::any())
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
        $loader->expects(self::any())
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
        $loader->expects(self::any())
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

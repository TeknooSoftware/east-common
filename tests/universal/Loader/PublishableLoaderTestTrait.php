<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Loader;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\PublishableLoaderInterface;

trait PublishableLoaderTestTrait
{
    use LoaderTestTrait;

    /**
     * @expectedException \Throwable
     */
    public function testLoadPublishedBadId()
    {
        $this->buildLoader()->loadPublished(new \stdClass(), new Promise());
    }

    /**
     * @expectedException \Throwable
     */
    public function testLoadPublishedBadPromise()
    {
        $this->buildLoader()->loadPublished(['fooBar'=>true], new \stdClass());
    }

    public function testLoadPublishedNotFound()
    {
        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findBy')
            ->with(['fooBar'=>true])
            ->willReturn(null);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail')
            ->with($this->callback(function ($exception) { return $exception instanceof \DomainException;}));

        self::assertInstanceOf(
            PublishableLoaderInterface::class,
            $this->buildLoader()->load(['fooBar'=>true], $promiseMock)
        );
    }

    public function testLoadPublishedFoundPublished()
    {
        $entity = $this->getEntity();
        $entity->setPublishedAt(new \DateTime('2017-06-17'));

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findBy')
            ->with(['fooBar'=>true])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with($entity);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            PublishableLoaderInterface::class,
            $this->buildLoader()->loadPublished(['fooBar'=>true], $promiseMock)
        );
    }

    public function testLoadPublishedFoundNotPublished()
    {
        $entity = $this->getEntity();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findBy')
            ->with(['fooBar'=>true])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail')
            ->with($this->callback(function ($exception) { return $exception instanceof \DomainException;}));

        self::assertInstanceOf(
            PublishableLoaderInterface::class,
            $this->buildLoader()->loadPublished(['fooBar'=>true], $promiseMock)
        );
    }
}
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

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\LoaderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Loader\ContentLoader
 * @covers      \Teknoo\East\Website\Loader\PublishableLoaderTrait
 * @covers      \Teknoo\East\Website\Loader\CollectionLoaderTrait
 */
class ContentLoaderTest extends \PHPUnit\Framework\TestCase
{
    use PublishableLoaderTestTrait;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    public function getRepositoryMock(): ObjectRepository
    {
        if (!$this->repository instanceof ObjectRepository) {
            $this->repository = $this->createMock(DocumentRepository::class);
        }

        return $this->repository;
    }

    /**
     * @return LoaderInterface|ContentLoader
     */
    public function buildLoader(): LoaderInterface
    {
        return new ContentLoader($this->getRepositoryMock());
    }

    /**
     * @return Content
     */
    public function getEntity()
    {
        return new Content();
    }

    public function testBySlugNotFound()
    {
        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with([
                'slug' => 'foo',
                'deletedAt'=>null,
            ])
            ->willReturn(null);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::never())->method('success');
        $promiseMock->expects(self::once())
            ->method('fail')
            ->with($this->callback(function ($exception) {
                return $exception instanceof \DomainException;
            }));

        self::assertInstanceOf(
            ContentLoader::class,
            $this->buildLoader()->bySlug('foo', $promiseMock)
        );
    }

    public function testBySlugFound()
    {
        $entity = $this->getEntity();
        $entity->setPublishedAt(new \DateTime('2017-07-01'))->updateStates();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with([
                'slug' => 'foo',
                'deletedAt'=>null,
            ])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with($entity);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            ContentLoader::class,
            $this->buildLoader()->bySlug('foo', $promiseMock)
        );
    }
}

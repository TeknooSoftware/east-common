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
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Loader\UserLoader;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Loader\UserLoader
 * @covers      \Teknoo\East\Website\Loader\CollectionLoaderTrait
 */
class UserLoaderTest extends \PHPUnit_Framework_TestCase
{
    use LoaderTestTrait;

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
            $this->repository = $this->createMock(ObjectRepository::class);
        }

        return $this->repository;
    }

    /**
     * @return LoaderInterface|UserLoader
     */
    public function buildLoader(): LoaderInterface
    {
        return new UserLoader($this->getRepositoryMock());
    }

    /**
     * @return User
     */
    public function getEntity()
    {
        return new User();
    }

    public function testByEmailNotFound()
    {
        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['email'=>'foo@bar'])
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
            UserLoader::class,
            $this->buildLoader()->byEmail('foo@bar', $promiseMock)
        );
    }

    public function testByEmailFound()
    {
        $entity = $this->getEntity();

        $this->getRepositoryMock()
            ->expects(self::any())
            ->method('findOneBy')
            ->with(['email'=>'foo@bar'])
            ->willReturn($entity);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $promiseMock
         *
         */
        $promiseMock = $this->createMock(Promise::class);
        $promiseMock->expects(self::once())->method('success')->with($entity);
        $promiseMock->expects(self::never())->method('fail');

        self::assertInstanceOf(
            UserLoader::class,
            $this->buildLoader()->byEmail('foo@bar', $promiseMock)
        );
    }
}
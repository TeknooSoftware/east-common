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

use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\LoaderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Loader\ContentLoader
 * @covers      \Teknoo\East\Website\Loader\LoaderTrait
 */
class ContentLoaderTest extends \PHPUnit\Framework\TestCase
{
    use LoaderTestTrait;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RepositoryInterface
     */
    public function getRepositoryMock(): RepositoryInterface
    {
        if (!$this->repository instanceof RepositoryInterface) {
            $this->repository = $this->createMock(RepositoryInterface::class);
        }

        return $this->repository;
    }

    /**
     * @return LoaderInterface|ContentLoader
     */
    public function buildLoader(): LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new ContentLoader($repository);
    }

    /**
     * @return Content
     */
    public function getEntity()
    {
        return new Content();
    }
}

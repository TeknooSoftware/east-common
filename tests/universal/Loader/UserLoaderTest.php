<?php

/**
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

namespace Teknoo\Tests\East\Common\Loader;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Common\Loader\UserLoader
 * @covers      \Teknoo\East\Common\Loader\LoaderTrait
 */
class UserLoaderTest extends TestCase
{
    use LoaderTestTrait;

    /**
     * @var \Teknoo\East\Common\Contracts\DBSource\RepositoryInterface
     */
    private $repository;

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|RepositoryInterface
     */
    public function getRepositoryMock(): RepositoryInterface
    {
        if (!$this->repository instanceof RepositoryInterface) {
            $this->repository = $this->createMock(UserRepositoryInterface::class);
        }

        return $this->repository;
    }

    /**
     * @return \Teknoo\East\Common\Contracts\Loader\LoaderInterface|UserLoader
     */
    public function buildLoader(): \Teknoo\East\Common\Contracts\Loader\LoaderInterface
    {
        $repository = $this->getRepositoryMock();
        return new UserLoader($repository);
    }

    /**
     * @return User
     */
    public function getEntity()
    {
        return new User();
    }
}

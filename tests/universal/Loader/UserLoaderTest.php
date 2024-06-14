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

namespace Teknoo\Tests\East\Common\Loader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\DBSource\Repository\UserRepositoryInterface;
use Teknoo\East\Common\Contracts\DBSource\RepositoryInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Loader\LoaderTrait;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(LoaderTrait::class)]
#[CoversClass(UserLoader::class)]
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

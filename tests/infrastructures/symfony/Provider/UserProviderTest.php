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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\User;
use Teknoo\East\WebsiteBundle\Provider\UserProvider;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Provider\UserProvider
 */
class UserProviderTest extends TestCase
{
    /**
     * @var UserLoader
     */
    private $loader;

    /**
     * @return UserLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLoader(): UserLoader
    {
        if (!$this->loader instanceof UserLoader) {
            $this->loader = $this->createMock(UserLoader::class);
        }

        return $this->loader;
    }

    public function buildProvider(): UserProvider
    {
        if (Kernel::VERSION_ID < 50000) {
            return new class ($this->getLoader()) extends UserProvider implements UserProviderInterface {
                public function loadUserByUsername($username)
                {
                    return $this->fetchUserByUsername($username);
                }
            };
        }

        return new class ($this->getLoader()) extends UserProvider implements UserProviderInterface {
            public function loadUserByUsername(string $username)
            {
                return $this->fetchUserByUsername($username);
            }
        };
    }

    public function testLoadUserByUsernameNotFound()
    {
        $this->expectException(UsernameNotFoundException::class);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->loadUserByUsername('foo@bar');
    }

    public function testLoadUserByUsernameFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        self::assertEquals(
            (new User($user)),
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testrefreshUserNotFound()
    {
        $this->expectException(UsernameNotFoundException::class);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->refreshUser(new User((new BaseUser())->setEmail('foo@bar')));
    }

    public function testrefreshUserFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        self::assertEquals(
            (new User($user)),
            $this->buildProvider()->refreshUser(new User((new BaseUser())->setEmail('foo@bar')))
        );
    }

    public function testSupportsClass()
    {
        self::assertTrue($this->buildProvider()->supportsClass(User::class));
        self::assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

<?php

/**
 * East Website.
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
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Website\Contracts\User\AuthDataInterface;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\WebsiteBundle\Provider\ThirdPartyAuthenticatedUserProvider;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Provider\ThirdPartyAuthenticatedUserProvider
 */
class ThirdPartyAuthenticatedUserProviderTest extends TestCase
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

    public function buildProvider(): ThirdPartyAuthenticatedUserProvider
    {
        return new ThirdPartyAuthenticatedUserProvider($this->getLoader());
    }

    public function testLoadUserByUsernameNotFound()
    {
        $this->expectException(UserNotFoundException::class);

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
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithoutThirdPartyAuth()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByUsername('foo@bar');
    }

    public function testLoadUserByIdentifierNotFound()
    {
        $this->expectException(UserNotFoundException::class);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->loadUserByIdentifier('foo@bar');
    }

    public function testLoadUserByIdentifierFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithoutThirdPartyAuth()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('foo@bar');
    }

    public function testrefreshUserNotFound()
    {
        $this->expectException(UserNotFoundException::class);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->refreshUser(
            new ThirdPartyAuthenticatedUser(
                (new BaseUser())->setEmail('foo@bar'),
                new ThirdPartyAuth()
            )
        );
    }

    public function testRefreshUserFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new ThirdPartyAuthenticatedUser(
                    (new BaseUser())->setEmail('foo@bar'),
                    $thirdPartyAuth
                )
            )
        );
    }

    public function testRefreshUserNotSupported()
    {
        self::assertNull(
            $this->buildProvider()->refreshUser($this->createMock(UserInterface::class))
        );
    }

    public function testSupportsClass()
    {
        self::assertTrue($this->buildProvider()->supportsClass(ThirdPartyAuthenticatedUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

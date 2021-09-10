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
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\LegacyUser;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\WebsiteBundle\Provider\PasswordAuthenticatedUserProvider;
use Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Provider\PasswordAuthenticatedUserProvider
 */
class PasswordAuthenticatedUserProviderTest extends TestCase
{
    /**
     * @var UserLoader
     */
    private $loader;

    /**
     * @var SymfonyUserWriter
     */
    private $writer;

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

    /**
     * @return SymfonyUserWriter|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWriter(): SymfonyUserWriter
    {
        if (!$this->writer instanceof SymfonyUserWriter) {
            $this->writer = $this->createMock(SymfonyUserWriter::class);
        }

        return $this->writer;
    }

    public function buildProvider(): PasswordAuthenticatedUserProvider
    {
        return new PasswordAuthenticatedUserProvider($this->getLoader(), $this->getWriter());
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
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithoutStoredPassword()
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

    public function testLoadLegacyUserByUsernameFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);
        $storedPassword->setSalt('foo');

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new LegacyUser($user, $storedPassword);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
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
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithoutStoredPassword()
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
            new PasswordAuthenticatedUser(
                (new BaseUser())->setEmail('foo@bar'),
                new StoredPassword()
            )
        );
    }

    public function testRefreshUserFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new PasswordAuthenticatedUser(
                    (new BaseUser())->setEmail('foo@bar'),
                    $storedPassword
                )
            )
        );
    }

    public function testRefreshLegacyUserFound()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);
        $storedPassword->setSalt('foo');

        $this->getLoader()
            ->expects(self::once())
            ->method('query')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new LegacyUser($user, $storedPassword);

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new LegacyUser(
                    (new BaseUser())->setEmail('foo@bar'),
                    $storedPassword
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

    public function testUpgradePasswordNotSupported()
    {
        $this->getWriter()->expects(self::never())->method('save');

        $this->buildProvider()->upgradePassword(
            $this->createMock(UserInterface::class),
            'foo'
        );
    }

    public function testUpgradePassword()
    {
        $user = $this->createMock(User::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $legacyUser = $this->createMock(LegacyUser::class);
        $legacyUser->expects(self::any())->method('getWrappedUser')->willReturn($user);
        $legacyUser->expects(self::any())->method('getWrappedStoredPassword')->willReturn($storedPassword);

        $storedPassword->expects(self::once())->method('setSalt')->with('');
        $storedPassword->expects(self::once())->method('setHashedPassword')->with('foo');

        $this->getWriter()->expects(self::once())->method('save');

        $this->buildProvider()->upgradePassword(
            $legacyUser,
            'foo'
        );
    }

    public function testSupportsClass()
    {
        self::assertTrue($this->buildProvider()->supportsClass(PasswordAuthenticatedUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

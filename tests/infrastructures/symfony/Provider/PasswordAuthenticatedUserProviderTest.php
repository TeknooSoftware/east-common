<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Provider;

use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Provider\PasswordAuthenticatedUserProvider;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers      \Teknoo\East\CommonBundle\Provider\PasswordAuthenticatedUserProvider
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
            ->method('fetch')
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
            ->method('fetch')
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

    public function testLoadUserByUsernameFoundWithGoogleAuthenticator()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $storedPassword = new StoredPassword(),
                $totp = new TOTPAuth(
                    provider: TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
                    algorithm: 'sha1',
                    period: 30,
                    digits: 6,
                    enabled: true,
                ),
            ],
        );

        $this->getLoader()
            ->expects(self::once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        self::assertTrue(
            $loadedUser->isEqualTo(
                $builtUser = $this->buildProvider()
                    ->loadUserByUsername('foo@bar')
            )
        );

        self::assertInstanceOf(
            GoogleTwoFactorInterface::class,
            $builtUser,
        );

        self::assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $builtUser,
        );
    }

    public function testLoadUserByUsernameFoundWithTOTP()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $storedPassword = new StoredPassword(),
                $totp = new TOTPAuth(
                    provider: 'aTotp',
                    algorithm: 'sha1',
                    period: 30,
                    digits: 6,
                    enabled: true,
                ),
            ]
        );

        $this->getLoader()
            ->expects(self::once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        self::assertTrue(
            $loadedUser->isEqualTo(
                $builtUser = $this->buildProvider()
                    ->loadUserByUsername('foo@bar')
            )
        );

        self::assertInstanceOf(
            TotpTwoFactorInterface::class,
            $builtUser,
        );

        self::assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $builtUser,
        );
    }

    public function testLoadUserByUsernameFoundWithoutStoredPassword()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects(self::once())
            ->method('fetch')
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
            ->method('fetch')
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
            ->method('fetch')
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
            ->method('fetch')
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
            ->method('fetch')
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
            ->method('fetch')
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
        $user = $this->createMock(PasswordAuthenticatedUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user->expects(self::any())->method('getWrappedStoredPassword')->willReturn($storedPassword);

        $storedPassword->expects(self::once())->method('setHashedPassword')->with('foo');

        $this->getWriter()->expects(self::once())->method('save');

        $this->buildProvider()->upgradePassword(
            $user,
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

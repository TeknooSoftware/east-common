<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\CommonBundle\Provider\PasswordAuthenticatedUserProvider;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(PasswordAuthenticatedUserProvider::class)]
class PasswordAuthenticatedUserProviderTest extends TestCase
{
    private (UserLoader&MockObject)|null $loader = null;

    private (SymfonyUserWriter&MockObject)|null $writer = null;

    public function getLoader(): UserLoader&MockObject
    {
        if (!$this->loader instanceof UserLoader) {
            $this->loader = $this->createMock(UserLoader::class);
        }

        return $this->loader;
    }

    public function getWriter(): SymfonyUserWriter&MockObject
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

    public function testLoadUserByUsernameNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->loadUserByUsername('foo@bar');
    }

    public function testLoadUserByUsernameFound(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithGoogleAuthenticator(): void
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
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        $this->assertTrue(
            $loadedUser->isEqualTo(
                $builtUser = $this->buildProvider()
                    ->loadUserByUsername('foo@bar')
            )
        );

        $this->assertInstanceOf(
            GoogleTwoFactorInterface::class,
            $builtUser,
        );

        $this->assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $builtUser,
        );
    }

    public function testLoadUserByUsernameFoundWithTOTP(): void
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
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        $this->assertTrue(
            $loadedUser->isEqualTo(
                $builtUser = $this->buildProvider()
                    ->loadUserByUsername('foo@bar')
            )
        );

        $this->assertInstanceOf(
            TotpTwoFactorInterface::class,
            $builtUser,
        );

        $this->assertInstanceOf(
            UserWithTOTPAuthInterface::class,
            $builtUser,
        );
    }

    public function testLoadUserByUsernameFoundWithoutStoredPassword(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByUsername('foo@bar');
    }

    public function testLoadUserByIdentifierNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->loadUserByIdentifier('foo@bar');
    }

    public function testLoadUserByIdentifierFound(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithoutStoredPassword(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('foo@bar');
    }

    public function testrefreshUserNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->fail(new \DomainException());

                return $this->getLoader();
            });

        $this->buildProvider()->refreshUser(
            new PasswordAuthenticatedUser(
                new BaseUser()->setEmail('foo@bar'),
                new StoredPassword()
            )
        );
    }

    public function testRefreshUserFound(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$storedPassword = new StoredPassword()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new PasswordAuthenticatedUser($user, $storedPassword);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new PasswordAuthenticatedUser(
                    new BaseUser()->setEmail('foo@bar'),
                    $storedPassword
                )
            )
        );
    }

    public function testRefreshUserNotSupported(): void
    {
        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($this->createMock(UserInterface::class));
    }

    public function testUpgradePasswordNotSupported(): void
    {
        $this->getWriter()->expects($this->never())->method('save');

        $this->buildProvider()->upgradePassword(
            $this->createMock(UserInterface::class),
            'foo'
        );
    }

    public function testUpgradePassword(): void
    {
        $user = $this->createMock(PasswordAuthenticatedUser::class);
        $storedPassword = $this->createMock(StoredPassword::class);

        $user->method('getWrappedStoredPassword')->willReturn($storedPassword);

        $storedPassword->expects($this->once())->method('setHashedPassword')->with('foo');

        $this->getWriter()->expects($this->once())->method('save');

        $this->buildProvider()->upgradePassword(
            $user,
            'foo'
        );
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->buildProvider()->supportsClass(PasswordAuthenticatedUser::class));
        $this->assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        $this->assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

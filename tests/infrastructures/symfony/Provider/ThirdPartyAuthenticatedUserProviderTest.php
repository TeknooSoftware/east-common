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

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\CommonBundle\Provider\ThirdPartyAuthenticatedUserProvider;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ThirdPartyAuthenticatedUserProvider::class)]
class ThirdPartyAuthenticatedUserProviderTest extends TestCase
{
    private (UserLoader&Stub)|(UserLoader&MockObject)|null $loader = null;

    public function getLoader(bool $stub = false): (UserLoader&Stub)|(UserLoader&MockObject)
    {
        if (!$this->loader instanceof UserLoader) {
            if ($stub) {
                $this->loader = $this->createStub(UserLoader::class);
            } else {
                $this->loader = $this->createMock(UserLoader::class);
            }
        }

        return $this->loader;
    }

    public function buildProvider(): ThirdPartyAuthenticatedUserProvider
    {
        return new ThirdPartyAuthenticatedUserProvider(
            $this->getLoader(true),
        );
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
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithoutThirdPartyAuth(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createStub(AuthDataInterface::class)]);

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
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithGooglAuth(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $thirdPartyAuth = new ThirdPartyAuth(),
                $totp = new TOTPAuth(
                    provider: TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
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

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

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

    public function testLoadUserByIdentifierFoundWithTOTP(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $thirdPartyAuth = new ThirdPartyAuth(),
                $totp = new TOTPAuth(
                    provider: 'aTotp',
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

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

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

    public function testLoadUserByIdentifierFoundWithoutThirdPartyAuth(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createStub(AuthDataInterface::class)]);

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
            new ThirdPartyAuthenticatedUser(
                new BaseUser()->setEmail('foo@bar'),
                new ThirdPartyAuth()
            )
        );
    }

    public function testRefreshUserFound(): void
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new ThirdPartyAuthenticatedUser(
                    new BaseUser()->setEmail('foo@bar'),
                    $thirdPartyAuth
                )
            )
        );
    }

    public function testRefreshUserNotSupported(): void
    {
        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($this->createStub(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->buildProvider()->supportsClass(ThirdPartyAuthenticatedUser::class));
        $this->assertTrue($this->buildProvider()->supportsClass(GoogleAuthThirdPartyAuthenticatedUser::class));
        $this->assertTrue($this->buildProvider()->supportsClass(TOTPThirdPartyAuthenticatedUser::class));
        $this->assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        $this->assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

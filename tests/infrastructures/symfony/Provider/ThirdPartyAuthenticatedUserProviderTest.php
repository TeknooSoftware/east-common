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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Provider;

use PHPUnit\Framework\Attributes\CoversClass;
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(ThirdPartyAuthenticatedUserProvider::class)]
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
            ->expects($this->once())
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
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
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
            ->expects($this->once())
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
            ->expects($this->once())
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
        $user->setAuthData([$thirdPartyAuth = new ThirdPartyAuth()]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
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
    public function testLoadUserByIdentifierFoundWithGooglAuth()
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

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

    public function testLoadUserByIdentifierFoundWithTOTP()
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);

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

    public function testLoadUserByIdentifierFoundWithoutThirdPartyAuth()
    {
        $user = new BaseUser();
        $user->setEmail('foo@bar');
        $user->setAuthData([$this->createMock(AuthDataInterface::class)]);

        $this->getLoader()
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('fetch')
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
            ->expects($this->once())
            ->method('fetch')
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
        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClass()
    {
        self::assertTrue($this->buildProvider()->supportsClass(ThirdPartyAuthenticatedUser::class));
        self::assertTrue($this->buildProvider()->supportsClass(GoogleAuthThirdPartyAuthenticatedUser::class));
        self::assertTrue($this->buildProvider()->supportsClass(TOTPThirdPartyAuthenticatedUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(BaseUser::class));
        self::assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

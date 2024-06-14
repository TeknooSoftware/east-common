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

namespace Teknoo\Tests\East\CommonBundle\Provider;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\Common\User\RecoveryAccess\TimeLimitedToken;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\CommonBundle\Provider\RecoveringAccessUserProvider;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RecoveringAccessUserProvider::class)]
class RecoveringAccessUserProviderTest extends TestCase
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
     * @var DatesService
     */
    private $datesService;

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

    /**
     * @return DatesService|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDatesService(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    public function buildProvider(): RecoveringAccessUserProvider
    {
        return new RecoveringAccessUserProvider(
            $this->getLoader(),
            $this->getWriter(),
            $this->getDatesService(),
            'ROLE_RECOVERY',
        );
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
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData([
            $recoveryAccess = new RecoveryAccess(
                algorithm: TimeLimitedToken::class,
                params: [
                    'expired_at' => '2024-01-31',
                    'token' => hash('sha256', random_bytes(512)),
                ],
            )
        ]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithWrongAlgorithm()
    {
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData([
            $recoveryAccess = new RecoveryAccess(
                algorithm: TimeLimitedToken::class,
                params: [
                    'expired_at' => '2024-01-26',
                    'token' => hash('sha256', random_bytes(512)),
                ],
            )
        ]);

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

    public function testLoadUserByUsernameFoundWithGoogleAuthenticator()
    {
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $recoveryAccess = new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [
                        'expired_at' => '2024-01-31',
                        'token' => hash('sha256', random_bytes(512)),
                    ],
                ),
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

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
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData(
            [
                $recoveryAccess = new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [
                        'expired_at' => '2024-01-31',
                        'token' => hash('sha256', random_bytes(512)),
                    ],
                ),
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

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

    public function testLoadUserByUsernameFoundWithoutRecoveryAccess()
    {
        $user = new User();
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
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData([
            $recoveryAccess = new RecoveryAccess(
                algorithm: TimeLimitedToken::class,
                params: [
                    'expired_at' => '2024-01-31',
                    'token' => hash('sha256', random_bytes(512)),
                ],
            )
        ]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithoutRecoveryAccess()
    {
        $user = new User();
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
            new UserWithRecoveryAccess(
                (new User())->setEmail('foo@bar'),
                $recoveryAccess = new RecoveryAccess(
                    algorithm: TimeLimitedToken::class,
                    params: [
                        'expired_at' => '2024-01-31',
                        'token' => hash('sha256', random_bytes(512)),
                    ],
                ),
                'ROLE_RECOVERY',
            )
        );
    }

    public function testRefreshUserFound()
    {
        $this->getDatesService()->expects($this->any())
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter) {
                    $setter(new DateTimeImmutable('2024-01-28'));

                    return  $this->datesService;
                }
            );

        $user = new User();
        $user->setEmail('foo@bar');
        $user->setAuthData([
            $recoveryAccess = new RecoveryAccess(
                algorithm: TimeLimitedToken::class,
                params: [
                    'expired_at' => '2024-01-31',
                    'token' => hash('sha256', random_bytes(512)),
                ],
            )
        ]);

        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user) {
                self::assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        self::assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new UserWithRecoveryAccess(
                    (new User())->setEmail('foo@bar'),
                    $recoveryAccess,
                    'ROLE_RECOVERY',
                )
            )
        );
    }

    public function testRefreshUserNotSupported()
    {
        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($this->createMock(UserInterface::class));
    }

    public function testUpgradePasswordNotSupported()
    {
        $this->getWriter()->expects($this->never())->method('save');

        $this->buildProvider()->upgradePassword(
            $this->createMock(UserInterface::class),
            'foo'
        );
    }

    public function testUpgradePassword()
    {
        $sfUser = $this->createMock(UserWithRecoveryAccess::class);
        $user = $this->createMock(User::class);

        $sfUser->expects($this->any())->method('getWrappedUser')->willReturn($user);

        $user->expects($this->once())
            ->method('addAuthData')
            ->with(
                new StoredPassword(
                    algo: PasswordAuthenticatedUser::class,
                    hash: 'foo',
                    unhashedPassword: false,
                )
            );

        $this->getWriter()->expects($this->once())->method('save');

        $this->buildProvider()->upgradePassword(
            $sfUser,
            'foo'
        );
    }

    public function testSupportsClass()
    {
        self::assertTrue($this->buildProvider()->supportsClass(UserWithRecoveryAccess::class));
        self::assertFalse($this->buildProvider()->supportsClass(User::class));
        self::assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

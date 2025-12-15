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

use DateTimeImmutable;
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(RecoveringAccessUserProvider::class)]
class RecoveringAccessUserProviderTest extends TestCase
{
    private (UserLoader&Stub)|(UserLoader&MockObject)|null $loader = null;

    private (SymfonyUserWriter&Stub)|(SymfonyUserWriter&MockObject)|null $writer = null;

    private (DatesService&Stub)|(DatesService&MockObject)|null $datesService = null;

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

    public function getWriter(bool $stub = false): (SymfonyUserWriter&Stub)|(SymfonyUserWriter&MockObject)
    {
        if (!$this->writer instanceof SymfonyUserWriter) {
            if ($stub) {
                $this->writer = $this->createStub(SymfonyUserWriter::class);
            } else {
                $this->writer = $this->createMock(SymfonyUserWriter::class);
            }
        }

        return $this->writer;
    }

    public function getDatesService(bool $stub = false): (DatesService&Stub)|(DatesService&MockObject)
    {
        if (!$this->datesService instanceof DatesService) {
            if ($stub) {
                $this->datesService = $this->createStub(DatesService::class);
            } else {
                $this->datesService = $this->createMock(DatesService::class);
            }
        }

        return $this->datesService;
    }

    public function buildProvider(): RecoveringAccessUserProvider
    {
        return new RecoveringAccessUserProvider(
            $this->getLoader(true),
            $this->getWriter(true),
            $this->getDatesService(true),
            'ROLE_RECOVERY',
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
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByUsernameFoundWithWrongAlgorithm(): void
    {
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByUsername('foo@bar');
    }

    public function testLoadUserByUsernameFoundWithGoogleAuthenticator(): void
    {
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

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
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

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

    public function testLoadUserByUsernameFoundWithoutRecoveryAccess(): void
    {
        $user = new User();
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
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->loadUserByUsername('foo@bar')
        );
    }

    public function testLoadUserByIdentifierFoundWithoutRecoveryAccess(): void
    {
        $user = new User();
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
            new UserWithRecoveryAccess(
                new User()->setEmail('foo@bar'),
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

    public function testRefreshUserFound(): void
    {
        $this->getDatesService(true)
            ->method('passMeTheDate')
            ->willReturnCallback(
                function (callable $setter): DatesService {
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
            ->willReturnCallback(function ($name, PromiseInterface $promise) use ($user): \Teknoo\East\Common\Loader\UserLoader {
                $this->assertEquals(new UserByEmailQuery('foo@bar'), $name);
                $promise->success($user);

                return $this->getLoader();
            });

        $loadedUser = new UserWithRecoveryAccess($user, $recoveryAccess, 'ROLE_RECOVERY');

        $this->assertEquals(
            $loadedUser,
            $this->buildProvider()->refreshUser(
                new UserWithRecoveryAccess(
                    new User()->setEmail('foo@bar'),
                    $recoveryAccess,
                    'ROLE_RECOVERY',
                )
            )
        );
    }

    public function testRefreshUserNotSupported(): void
    {
        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($this->createStub(UserInterface::class));
    }

    public function testUpgradePasswordNotSupported(): void
    {
        $this->getWriter()->expects($this->never())->method('save');

        $this->buildProvider()->upgradePassword(
            $this->createStub(UserInterface::class),
            'foo'
        );
    }

    public function testUpgradePassword(): void
    {
        $sfUser = $this->createStub(UserWithRecoveryAccess::class);
        $user = $this->createMock(User::class);

        $sfUser->method('getWrappedUser')->willReturn($user);

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

    public function testSupportsClass(): void
    {
        $this->assertTrue($this->buildProvider()->supportsClass(UserWithRecoveryAccess::class));
        $this->assertFalse($this->buildProvider()->supportsClass(User::class));
        $this->assertFalse($this->buildProvider()->supportsClass(\DateTime::class));
    }
}

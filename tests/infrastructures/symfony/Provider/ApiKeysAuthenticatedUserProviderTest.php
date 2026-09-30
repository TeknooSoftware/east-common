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
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Provider\ApiKeysAuthenticatedUserProvider;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeysAuthenticatedUserProvider::class)]
class ApiKeysAuthenticatedUserProviderTest extends TestCase
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

    public function buildProvider(): ApiKeysAuthenticatedUserProvider
    {
        return new ApiKeysAuthenticatedUserProvider(
            $this->getLoader(true),
            new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01 12:00:00')),
        );
    }

    private function createUser(ApiKeyToken ...$tokens): User
    {
        return new User()
            ->setEmail('foo@bar')
            ->setAuthData([
                new StoredPassword(),
                new ApiKeysAuth($tokens),
            ]);
    }

    private function createToken(string $name, string $expiresAt, bool $isExpired = false): ApiKeyToken
    {
        return new ApiKeyToken(
            name: $name,
            token: 'secret',
            tokenHash: $name . '-hash',
            isExpired: $isExpired,
            expiresAt: new DateTimeImmutable($expiresAt),
        );
    }

    private function expectLoaderToFetch(User $user): void
    {
        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (UserByEmailQuery $query, PromiseInterface $promise) use ($user): UserLoader {
                    $this->assertEquals(new UserByEmailQuery('foo@bar'), $query);
                    $promise->success($user);

                    return $this->getLoader();
                },
            );
    }

    public function testLoadUserByIdentifierWithoutTokenName(): void
    {
        $this->getLoader()->expects($this->never())->method('fetch');

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('foo@bar');
    }

    public function testLoadUserByUsernameWithEmptyTokenName(): void
    {
        $this->getLoader()->expects($this->never())->method('fetch');

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByUsername(':foo@bar');
    }

    public function testLoadUserByIdentifierNotFound(): void
    {
        $this->getLoader()
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(
                function (UserByEmailQuery $query, PromiseInterface $promise): UserLoader {
                    $promise->fail(new \DomainException());

                    return $this->getLoader();
                },
            );

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithValidToken(): void
    {
        $user = $this->createUser(
            $this->createToken('alice', '2025-01-01'),
            $this->createToken('bob', '2025-01-01'),
        );
        $this->expectLoaderToFetch($user);

        $loaded = $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');

        $this->assertInstanceOf(ApiKeysAuthUser::class, $loaded);
        $this->assertSame($user, $loaded->getWrappedUser());
        $this->assertSame('bob-hash', $loaded->getPassword());
    }

    public function testLoadUserByUsernameWithValidTokenInAnApiKeysAuthSubclass(): void
    {
        $token = $this->createToken('bob', '2025-01-01');
        $user = new User()
            ->setEmail('foo@bar')
            ->setAuthData([
                new class ([$token]) extends ApiKeysAuth {
                },
            ]);
        $this->expectLoaderToFetch($user);

        $loaded = $this->buildProvider()->loadUserByUsername('bob:foo@bar');

        $this->assertInstanceOf(ApiKeysAuthUser::class, $loaded);
        $this->assertSame('bob-hash', $loaded->getPassword());
    }

    public function testLoadUserByIdentifierWithExpiredToken(): void
    {
        $this->expectLoaderToFetch($this->createUser($this->createToken('bob', '2023-01-01')));

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithTokenMarkedAsExpired(): void
    {
        $this->expectLoaderToFetch($this->createUser($this->createToken('bob', '2025-01-01', true)));

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithMismatchedTokenName(): void
    {
        $this->expectLoaderToFetch($this->createUser($this->createToken('alice', '2025-01-01')));

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');
    }

    public function testLoadUserByIdentifierWithoutTokens(): void
    {
        $this->expectLoaderToFetch($this->createUser());

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->loadUserByIdentifier('bob:foo@bar');
    }

    public function testRefreshUserWithApiKeysAuthUser(): void
    {
        //The user is reloaded with the key whose hash is its password
        $user = $this->createUser(
            $this->createToken('alice', '2025-01-01'),
            $token = $this->createToken('bob', '2025-01-01'),
        );
        $this->expectLoaderToFetch($user);

        $refreshed = $this->buildProvider()->refreshUser(new ApiKeysAuthUser($user, $token));

        $this->assertInstanceOf(ApiKeysAuthUser::class, $refreshed);
        $this->assertSame($user, $refreshed->getWrappedUser());
        $this->assertSame('bob-hash', $refreshed->getPassword());
    }

    public function testRefreshUserWithApiKeysAuthUserWhenItsTokenHasBeenRemoved(): void
    {
        $token = $this->createToken('bob', '2025-01-01');
        $this->expectLoaderToFetch($this->createUser($this->createToken('alice', '2025-01-01')));

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->refreshUser(new ApiKeysAuthUser(new User()->setEmail('foo@bar'), $token));
    }

    public function testRefreshUserWithApiKeysAuthUserWhenItsTokenIsExpired(): void
    {
        $user = $this->createUser($token = $this->createToken('bob', '2023-01-01'));
        $this->expectLoaderToFetch($user);

        $this->expectException(UserNotFoundException::class);
        $this->buildProvider()->refreshUser(new ApiKeysAuthUser($user, $token));
    }

    public function testRefreshUserWithOtherUser(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('foo@bar');

        $this->getLoader()->expects($this->never())->method('fetch');

        $this->expectException(MissingUserException::class);
        $this->buildProvider()->refreshUser($user);
    }

    public function testSupportsClass(): void
    {
        $provider = $this->buildProvider();

        $this->assertTrue($provider->supportsClass(ApiKeysAuthUser::class));
        $this->assertTrue(
            $provider->supportsClass(
                new class (new User(), new ApiKeyToken()) extends ApiKeysAuthUser {
                }::class
            )
        );
        $this->assertFalse($provider->supportsClass(PasswordAuthenticatedUser::class));
    }
}

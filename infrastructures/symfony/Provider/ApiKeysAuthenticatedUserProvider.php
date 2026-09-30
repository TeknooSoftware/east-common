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

namespace Teknoo\East\CommonBundle\Provider;

use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;

use function explode;
use function hash_equals;
use function str_contains;

/**
 * Symfony user provider to load East Common's user authenticated via an API key, stored into an ApiKeysAuth instance.
 * The identifier passed to this provider must be `key name:email` (like `my-key:user@domain.tld`). The user is
 * loaded only if the key exists, is not expired and is not marked as expired. It is dedicated to be used with a
 * `json_login` authenticator, to get a JWT token without the user's password.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements UserProviderInterface<ApiKeysAuthUser>
 */
class ApiKeysAuthenticatedUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserLoader $loader,
        private readonly DatesService $datesService,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->fetchUserByUsername($identifier);
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->fetchUserByUsername($username);
    }

    /**
     * @param callable(ApiKeysAuth): ?ApiKeyToken $tokenFinder to select the key of the user in its ApiKeysAuth
     */
    private function fetchUserByEmail(string $email, callable $tokenFinder): ApiKeysAuthUser
    {
        /** @var Promise<User, ApiKeysAuthUser, mixed> $promise */
        $promise = new Promise(function (User $user) use ($tokenFinder): ?ApiKeysAuthUser {
            $tokens = [];

            $this->datesService->passMeTheDate(
                static function (DateTimeInterface $now) use ($user, $tokenFinder, &$tokens): void {
                    foreach ($user->getAuthData() as $authData) {
                        if (
                            $authData instanceof ApiKeysAuth
                            && ($token = $tokenFinder($authData)) instanceof ApiKeyToken
                            && $token->getExpiresAt() > $now
                            && !$token->isExpired()
                        ) {
                            $tokens[] = $token;

                            break;
                        }
                    }
                }
            );

            if ([] === $tokens) {
                return null;
            }

            return new ApiKeysAuthUser($user, $tokens[0]);
        });

        $this->loader->fetch(
            new UserByEmailQuery($email),
            $promise,
        );

        $loadedUser = $promise->fetchResult();
        if (!$loadedUser instanceof ApiKeysAuthUser) {
            throw new UserNotFoundException();
        }

        return $loadedUser;
    }

    protected function fetchUserByUsername(string $username): ApiKeysAuthUser
    {
        if (!str_contains($username, ':')) {
            throw new UserNotFoundException();
        }

        [$tokenName, $username] = explode(':', $username);

        if (empty($tokenName)) {
            throw new UserNotFoundException();
        }

        return $this->fetchUserByEmail(
            $username,
            static fn (ApiKeysAuth $apiKeys): ?ApiKeyToken => $apiKeys->getToken($tokenName),
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof ApiKeysAuthUser) {
            //The name of the key is not available from the user : its key is found with its hash, already known
            //by the user as its password
            $tokenHash = $user->getPassword();

            return $this->fetchUserByEmail(
                $user->getUserIdentifier(),
                static function (ApiKeysAuth $apiKeys) use ($tokenHash): ?ApiKeyToken {
                    foreach ($apiKeys->getTokens() as $token) {
                        if (hash_equals($token->getTokenHash(), $tokenHash)) {
                            return $token;
                        }
                    }

                    return null;
                },
            );
        }

        throw new MissingUserException(
            "{$user->getUserIdentifier()} is not available with the provider " . self::class,
        );
    }

    /**
     * @param class-string<ApiKeysAuthUser> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        return ApiKeysAuthUser::class === $class
            || new ReflectionClass($class)->isSubclassOf(ApiKeysAuthUser::class);
    }
}

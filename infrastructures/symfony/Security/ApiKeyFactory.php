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

namespace Teknoo\East\CommonBundle\Security;

use DateTimeInterface;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Teknoo\East\Common\Contracts\User\ApiKeyFactoryInterface;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function bin2hex;
use function hash;
use function in_array;
use function preg_match;
use function random_bytes;

/**
 * Default implementation of `ApiKeyFactoryInterface`. The value of a key is the prefix of keys followed by 32 random
 * bytes in hexadecimal. Its SHA-256 digest finds the key without a slow hash, and its hash, with the hasher of
 * `ApiKeysAuthUser` (section `password_hashers` of Symfony Security), keeps the JWT flow. When the application lists
 * scopes, each key must have one scope of this list. Without this list, a key has no scope.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeyFactory implements ApiKeyFactoryInterface
{
    /**
     * @param array<int, string> $scopes
     */
    public function __construct(
        private readonly DatesService $datesService,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly string $tokenPrefix = '',
        private readonly array $scopes = [],
        private readonly bool $preferRealDate = false,
    ) {
    }

    public function create(User $owner, ApiKeyToken $apiKey, PromiseInterface $promise): ApiKeyFactoryInterface
    {
        if (1 !== preg_match('/^[a-z][a-z0-9\_\-]{3,}$/', $apiKey->getName())) {
            $promise->fail(new DomainException('teknoo.east.common.api_keys.form.name.regex_error', 400));

            return $this;
        }

        $scopeIsValid = null === $apiKey->getScope();
        if ([] !== $this->scopes) {
            $scopeIsValid = in_array($apiKey->getScope(), $this->scopes, true);
        }

        if (!$scopeIsValid) {
            $promise->fail(new DomainException('teknoo.east.common.api_keys.error.invalid_scope', 400));

            return $this;
        }

        $apiKeys = null;
        foreach ($owner->getAuthData() as $authData) {
            if ($authData instanceof ApiKeysAuth) {
                $apiKeys = $authData;

                break;
            }
        }

        $apiKeys ??= new ApiKeysAuth();

        if (null !== $apiKeys->getToken($apiKey->getName())) {
            $promise->fail(new DomainException('teknoo.east.common.api_keys.error.already_exists', 400));

            return $this;
        }

        try {
            $this->datesService->passMeTheDate(
                function (DateTimeInterface $now) use ($owner, $apiKey, $apiKeys): void {
                    $token = $this->tokenPrefix . bin2hex(random_bytes(32));

                    $apiKey->setToken($token);
                    $apiKey->setTokenDigest(hash('sha256', $token));
                    $apiKey->setCreatedAt($now);
                    $apiKey->setExpired(false);

                    $apiKey->setTokenHash(
                        $this->passwordHasher->hashPassword(
                            new ApiKeysAuthUser(
                                $owner,
                                $apiKey,
                            ),
                            $token
                        )
                    );

                    $apiKeys->addToken($apiKey);
                    $owner->addAuthData($apiKeys);
                },
                $this->preferRealDate,
            );
        } catch (Throwable $error) {
            $promise->fail($error);

            return $this;
        }

        $promise->success($apiKey);

        return $this;
    }
}

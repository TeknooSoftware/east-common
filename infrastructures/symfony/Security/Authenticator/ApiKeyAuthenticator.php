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

namespace Teknoo\East\CommonBundle\Security\Authenticator;

use DateTimeInterface;
use DomainException;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByApiKeyDigestQuery;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function hash;
use function hash_equals;
use function in_array;
use function preg_match;
use function trim;

/**
 * Stateless Symfony authenticator for the API keys of users, sent by an API client with each request, in the header
 * `Authorization: Bearer`, or in a second header configured on the firewall (option `second_header`). Each request of
 * the firewall is authenticated : a request without key is refused. The key is found, without cache and without a slow
 * hash, from the SHA-256 digest of its value, with one indexed query (`UserByApiKeyDigestQuery`). The key must not be
 * expired, its user must be active and, when the firewall lists accepted scopes (option `accepted_scopes`), its scope
 * must be in this list. The user does not need a password. The token gets the name and the scope of the key, in the
 * attributes `api_key_name` and `api_key_scope`. Failures are rendered by the service of the firewall option
 * `failure_handler`. A failure of the database gives an `AuthenticationServiceException`, and not a refused key, so
 * the failure handler can answer with another status. No message holds the value of the key.
 *
 * This authenticator is configured with the key `api_key` of a firewall, thanks to `ApiKeyAuthenticatorFactory`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    /**
     * @param array<int, string> $acceptedScopes
     */
    public function __construct(
        private readonly UserLoader $loader,
        private readonly DatesService $datesService,
        private readonly AuthenticationFailureHandlerInterface $failureHandler,
        private readonly ?string $secondHeader = null,
        private readonly array $acceptedScopes = [],
        private readonly bool $preferRealDate = true,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $value = '';
        $matches = [];
        if (1 === preg_match('/^Bearer\s+(.*)$/i', (string) $request->headers->get('Authorization', ''), $matches)) {
            $value = trim($matches[1]);
        }

        $secondValue = '';
        if (null !== $this->secondHeader) {
            $secondValue = trim((string) $request->headers->get($this->secondHeader, ''));
        }

        if ('' !== $value && '' !== $secondValue && !hash_equals($value, $secondValue)) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.different_values');
        }

        if ('' === $value) {
            $value = $secondValue;
        }

        if ('' === $value) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.missing');
        }

        $digest = hash('sha256', $value);

        /** @var Promise<User, User|Throwable, mixed> $promise */
        $promise = new Promise(
            static fn (User $user): User => $user,
            static fn (#[SensitiveParameter] Throwable $error): Throwable => $error,
        );

        $this->loader->fetch(new UserByApiKeyDigestQuery($digest), $promise);

        $user = $promise->fetchResult();
        if ($user instanceof Throwable && !$user instanceof DomainException) {
            //A repository gives a DomainException when no user is found. Another error is a failure of the database
            throw new AuthenticationServiceException('teknoo.east.common.api_keys.error.unavailable', 0, $user);
        }

        if (!$user instanceof User || !$user->isActive()) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.invalid');
        }

        $apiKey = null;
        foreach ($user->getAuthData() as $authData) {
            if (!$authData instanceof ApiKeysAuth) {
                continue;
            }

            foreach ($authData->getTokens() as $token) {
                if (hash_equals($token->getTokenDigest() ?? '', $digest)) {
                    $apiKey = $token;

                    break 2;
                }
            }
        }

        if (!$apiKey instanceof ApiKeyToken) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.invalid');
        }

        $isValid = false;
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $now) use ($apiKey, &$isValid): void {
                $isValid = !$apiKey->isExpired() && $apiKey->getExpiresAt() > $now;
            },
            $this->preferRealDate,
        );

        if (!$isValid) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.invalid');
        }

        if ([] !== $this->acceptedScopes && !in_array($apiKey->getScope(), $this->acceptedScopes, true)) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.api_keys.error.scope_refused');
        }

        $symfonyUser = new ApiKeysAuthUser($user, $apiKey);

        $passport = new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                static fn (): ApiKeysAuthUser => $symfonyUser,
            )
        );

        $passport->setAttribute('api_key_name', $apiKey->getName());
        $passport->setAttribute('api_key_scope', $apiKey->getScope());

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = parent::createToken($passport, $firewallName);

        $token->setAttribute('api_key_name', $passport->getAttribute('api_key_name'));
        $token->setAttribute('api_key_scope', $passport->getAttribute('api_key_scope'));

        return $token;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }
}

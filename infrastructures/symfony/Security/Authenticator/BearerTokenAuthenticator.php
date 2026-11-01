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
use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\BearerTokensAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByBearerTokenDigestQuery;
use Teknoo\East\CommonBundle\Object\BearerTokenAuthenticatedUser;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function hash;
use function hash_equals;
use function preg_match;
use function trim;

/**
 * Stateless Symfony authenticator for the bearer tokens of users, sent by an API client with each request, in the
 * header `Authorization: Bearer`, or in a second header configured on the firewall (option `second_header`). Each
 * request of the firewall is authenticated : a request without key is refused. The key is found, without cache and
 * without a slow hash, from the SHA-256 digest of its value, with one indexed query (`UserByBearerTokenDigestQuery`).
 * The key must not be expired and its user must be active. The user does not need a password. The token gets the name
 * of the key, in the attribute `bearer_token_name`. Failures are rendered by the service of the firewall option
 * `failure_handler`. A failure of the database gives an `AuthenticationServiceException`, and not a refused key, so
 * the failure handler can answer with another status. No message holds the value of the key.
 *
 * This authenticator is configured with the key `bearer_token` of a firewall, thanks to
 * `BearerTokenAuthenticatorFactory`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserLoader $loader,
        private readonly DatesService $datesService,
        private readonly AuthenticationFailureHandlerInterface $failureHandler,
        private readonly ?string $secondHeader = null,
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
            throw new CustomUserMessageAuthenticationException(
                'teknoo.east.common.bearer_tokens.error.different_values'
            );
        }

        if ('' === $value) {
            $value = $secondValue;
        }

        if ('' === $value) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.bearer_tokens.error.missing');
        }

        $digest = hash('sha256', $value);

        /** @var Promise<User, User|Throwable, mixed> $promise */
        $promise = new Promise(
            static fn (User $user): User => $user,
            static fn (#[SensitiveParameter] Throwable $error): Throwable => $error,
        );

        $this->loader->fetch(new UserByBearerTokenDigestQuery($digest), $promise);

        $user = $promise->fetchResult();
        if ($user instanceof Throwable && !$user instanceof DomainException) {
            //A repository gives a DomainException when no user is found. Another error is a failure of the database
            throw new AuthenticationServiceException('teknoo.east.common.bearer_tokens.error.unavailable', 0, $user);
        }

        if (!$user instanceof User || !$user->isActive()) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.bearer_tokens.error.invalid');
        }

        $bearerToken = null;
        foreach ($user->getAuthData() as $authData) {
            if (!$authData instanceof BearerTokensAuth) {
                continue;
            }

            foreach ($authData->getTokens() as $token) {
                if (hash_equals($token->getTokenDigest() ?? '', $digest)) {
                    $bearerToken = $token;

                    break 2;
                }
            }
        }

        if (!$bearerToken instanceof BearerToken) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.bearer_tokens.error.invalid');
        }

        $isValid = false;
        $this->datesService->passMeTheDate(
            static function (DateTimeInterface $now) use ($bearerToken, &$isValid): void {
                $isValid = !$bearerToken->isExpired() && $bearerToken->getExpiresAt() > $now;
            },
            $this->preferRealDate,
        );

        if (!$isValid) {
            throw new CustomUserMessageAuthenticationException('teknoo.east.common.bearer_tokens.error.invalid');
        }

        $symfonyUser = new BearerTokenAuthenticatedUser($user, $bearerToken);

        $passport = new SelfValidatingPassport(
            new UserBadge(
                $symfonyUser->getUserIdentifier(),
                static fn (): BearerTokenAuthenticatedUser => $symfonyUser,
            )
        );

        $passport->setAttribute('bearer_token_name', $bearerToken->getName());

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = parent::createToken($passport, $firewallName);

        $token->setAttribute('bearer_token_name', $passport->getAttribute('bearer_token_name'));

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

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

use DomainException;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator as BaseAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Token\AccessToken;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function strtr;

/**
 * Symfony Authenticator build on KNPU OAuth2 client bundlle to create/update, thanks to a provided
 * implementation of `UserConverterInterface`, a East Common User instance and complete User's AuthParts
 * with a ThirdPartyAuth to store the access token.
 * If the user is not already present in the database (fetch from its email), it will be imported.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class OAuth2Authenticator extends BaseAuthenticator
{
    private const string PROTOCOL = 'oauth2';

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly UserLoader $loader,
        private readonly SymfonyUserWriter $userWriter,
        private readonly UserConverterInterface $userConverter,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return !empty($request->attributes->get('_oauth_client_key', false));
    }

    /**
     * @param PromiseInterface<ThirdPartyAuthenticatedUser, UserInterface> $promise
     */
    public function registerToken(User $user, string $provider, string $accessToken, PromiseInterface $promise): self
    {
        $totpAuth = null;
        $thirdPartyAuth = null;
        foreach ($user->getAuthData() as $authData) {
            if ($authData instanceof TOTPAuth) {
                $totpAuth = $authData;

                continue;
            }

            if (
                $authData instanceof ThirdPartyAuth
                && $authData->getProvider() === $provider
                && $authData->getProtocol() === self::PROTOCOL
            ) {
                $thirdPartyAuth = $authData;
            }
        }

        if (null === $thirdPartyAuth) {
            $thirdPartyAuth = new ThirdPartyAuth();

            $thirdPartyAuth->setProtocol(self::PROTOCOL);
            $thirdPartyAuth->setProvider($provider);
            $user->addAuthData($thirdPartyAuth);
        }

        $thirdPartyAuth->setToken($accessToken);

        $this->userWriter->save($user);

        if (
            $totpAuth instanceof TOTPAuth
            && (
                interface_exists(GoogleTwoFactorInterface::class)
                || interface_exists(TotpTwoFactorInterface::class)
            )
        ) {
            if (
                interface_exists(GoogleTwoFactorInterface::class)
                && TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR === $totpAuth->getProvider()
            ) {
                $user = new GoogleAuthThirdPartyAuthenticatedUser(
                    $user,
                    $thirdPartyAuth,
                );
            } else {
                $user = new TOTPThirdPartyAuthenticatedUser(
                    $user,
                    $thirdPartyAuth,
                );
            }

            $finalUser = $user->setTOTPAuth($totpAuth);
        } else {
            $finalUser = new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);
        }

        $promise->success($finalUser);

        return $this;
    }

    public function authenticate(Request $request): Passport
    {
        $provider = (string) $request->attributes->get('_oauth_client_key', '');
        $client = $this->clientRegistry->getClient($provider);

        /** @var AccessToken $accessToken */
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge(
                $accessToken->getToken(),
                function () use ($accessToken, $provider, $client): UserInterface {
                    $oauthUser = $client->fetchUserFromToken($accessToken);

                    $returnPromise = new Promise(
                        static fn (ThirdPartyAuthenticatedUser $user): ThirdPartyAuthenticatedUser => $user,
                        static function (#[SensitiveParameter] Throwable $error): never {
                            throw $error;
                        }
                    );

                    $registerTokenPromise = new Promise(
                        onSuccess: function (
                            User $user,
                            PromiseInterface $next
                        ) use (
                            $accessToken,
                            $provider
                        ): void {
                            $this->registerToken($user, $provider, $accessToken->getToken(), $next);
                        },
                        onFail: static fn (
                            #[SensitiveParameter] Throwable $error,
                            PromiseInterface $next,
                        ): PromiseInterface => $next->fail($error),
                    );

                    $fetchingPromise = new Promise(
                        onSuccess: static function (User $user, PromiseInterface $next): void {
                            $next->success($user);
                        },
                        onFail: function (
                            #[SensitiveParameter] Throwable $error,
                            PromiseInterface $next,
                        ) use ($oauthUser): void {
                            if ($error instanceof DomainException) {
                                $this->userConverter->convertToUser(
                                    $oauthUser,
                                    $next
                                );

                                return;
                            }

                            $next->fail($error);
                        },
                    );

                    $extractEmailPromise = new Promise(
                        function (string $email, PromiseInterface $next): void {
                            $this->loader->fetch(
                                new UserByEmailQuery($email),
                                $next
                            );
                        },
                        static function (#[SensitiveParameter] Throwable $error): never {
                            throw $error;
                        },
                        true,
                    );

                    /** @var Promise<string, UserInterface, mixed> $promise */
                    $promise = $extractEmailPromise
                        ->next(promise: $fetchingPromise)
                        ->next(promise: $registerTokenPromise)
                        ->next(promise: $returnPromise);

                    $this->userConverter->extractEmail(
                        $oauthUser,
                        $promise,
                    );

                    return $promise->fetchResult() ?? throw new AuthenticationException('User unavailable');
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr((string) $exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}

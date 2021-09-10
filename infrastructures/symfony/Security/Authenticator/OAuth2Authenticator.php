<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator as BaseAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Contracts\Security\Authenticator\UserConverterInterface;
use Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function strtr;

/**
 * Symfony Authenticator build on KNPU OAuth2 client bundlle to create/update, thanks to a provided
 * implementation of `UserConverterInterface`, a East Website User instance and complete User's AuthParts
 * with a ThirdPartyAuth to store the access token.
 * If the user is not already present in the database (fetch from its email), it will be imported.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class OAuth2Authenticator extends BaseAuthenticator
{
    private const PROTOCOL = 'oauth2';

    public function __construct(
        private ClientRegistry $clientRegistry,
        private UserLoader $loader,
        private SymfonyUserWriter $userWriter,
        private UserConverterInterface $userConverter,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return !empty($request->attributes->get('_oauth_client_key', false));
    }

    public function registerToken(User $user, string $provider, string $accessToken, PromiseInterface $promise): self
    {
        $thirdPartyAuth = null;
        foreach ($user->getAuthData() as $authData) {
            if (
                !$authData instanceof ThirdPartyAuth
                || $authData->getProvider() != $provider
                || $authData->getProtocol() != static::PROTOCOL
            ) {
                continue;
            }

            $thirdPartyAuth = $authData;
            break;
        }

        if (null === $thirdPartyAuth) {
            $thirdPartyAuth = new ThirdPartyAuth();

            $thirdPartyAuth->setProtocol(static::PROTOCOL);
            $thirdPartyAuth->setProvider($provider);
            $user->addAuthData($thirdPartyAuth);
        }

        $thirdPartyAuth->setToken($accessToken);

        $this->userWriter->save($user);

        $promise->success(new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth));

        return $this;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $provider = $request->attributes->get('_oauth_client_key', '');
        $client = $this->clientRegistry->getClient($provider);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge(
                $accessToken->getToken(),
                function () use ($accessToken, $provider, $client): UserInterface {
                    $oauthUser = $client->fetchUserFromToken($accessToken);

                    $returnPromise = new Promise(
                        static function (ThirdPartyAuthenticatedUser $user): ThirdPartyAuthenticatedUser {
                            return $user;
                        },
                        static function (Throwable $error) {
                            throw $error;
                        }
                    );

                    $registerTokenPromise = new Promise(
                        function (User $user, PromiseInterface $next) use ($accessToken, $provider) {
                            $this->registerToken($user, $provider, $accessToken->getToken(), $next);
                        },
                        fn (Throwable $error, PromiseInterface $next) => $next->fail($error),
                        true
                    );

                    $fetchingPromise = new Promise(
                        static function (User $user, PromiseInterface $next) {
                            $next->success($user);
                        },
                        function (Throwable $error, PromiseInterface $next) use ($oauthUser) {
                            if (!$error instanceof \DomainException) {
                                $next->fail($error);
                            }

                            $this->userConverter->convertToUser(
                                $oauthUser,
                                $next
                            );
                        },
                        true,
                    );

                    $extractEmailPromise = new Promise(
                        function (string $email, PromiseInterface $next) {
                            $this->loader->query(
                                new UserByEmailQuery($email),
                                $next
                            );
                        },
                        static function (Throwable $error) {
                            throw $error;
                        },
                        true,
                    );

                    $this->userConverter->extractEmail(
                        $oauthUser,
                        $promise = $extractEmailPromise->next(
                            $fetchingPromise->next(
                                $registerTokenPromise->next(
                                    $returnPromise
                                )
                            )
                        )
                    );

                    return $promise->fetchResult();
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
        $message = strtr((string) $exception->getMessageKey(), (array) $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }
}

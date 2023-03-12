<?php

/*
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Provider;

use ReflectionException;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPThirdPartyAuthenticatedUser;
use Teknoo\Recipe\Promise\Promise;

use function interface_exists;

/**
 * Symfony user provider to load East Common's user authenticated thanks to OAuth2Authenticator, or any third party
 * authenticated. It can manage only ThirdPartyAuthenticatedUser.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ThirdPartyAuthenticatedUserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly UserLoader $loader,
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

    protected function fetchUserByUsername(string $username): UserInterface
    {
        /** @var Promise<User, ThirdPartyAuthenticatedUser, mixed> $promise */
        $promise = new Promise(static function (User $user): ?ThirdPartyAuthenticatedUser {
            $totpAuth = null;
            $thirdPartyAuth = null;

            foreach ($user->getAuthData() as $authData) {
                if ($authData instanceof TOTPAuth) {
                    $totpAuth = $authData;
                }

                if ($authData instanceof ThirdPartyAuth) {
                    $thirdPartyAuth = $authData;
                }
            }

            if (null === $thirdPartyAuth) {
                return null;
            }

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

                return $user->setTOTPAuth($totpAuth);
            }

            return new ThirdPartyAuthenticatedUser($user, $thirdPartyAuth);
        });

        $this->loader->fetch(
            new UserByEmailQuery($username),
            $promise,
        );

        $loadedUser = $promise->fetchResult();
        if (!$loadedUser instanceof ThirdPartyAuthenticatedUser) {
            throw new UserNotFoundException();
        }

        return $loadedUser;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        if ($user instanceof ThirdPartyAuthenticatedUser) {
            return $this->fetchUserByUsername($user->getUserIdentifier());
        }

        return null;
    }

    /**
     * @param class-string<ThirdPartyAuthenticatedUser> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        return ThirdPartyAuthenticatedUser::class === $class;
    }
}

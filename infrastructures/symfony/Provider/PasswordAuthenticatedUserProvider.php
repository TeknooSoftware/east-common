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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Provider;

use ReflectionClass;
use ReflectionException;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractPasswordAuthUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPPasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

use function interface_exists;

/**
 * Symfony user provider to load East Common's user from email.
 * Use the standard User Loader, and wrap user fetched into a PasswordAuthenticatedUser or a LegacyUser.
 * A LegacyUser is returned when the user is authenticated with the legacy couple of salt+password hashed thanks to
 * pbkdf2.
 * A PasswordAuthenticatedUser is returned for all user authenticated thanks to a modern hash method like sodium.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PasswordAuthenticatedUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserLoader $loader,
        private readonly SymfonyUserWriter $userWriter,
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
        /** @var Promise<User, PasswordAuthenticatedUser, mixed> $promise */
        $promise = new Promise(static function (User $user): ?PasswordAuthenticatedUser {
            $totpAuth = null;
            $storedPassword = null;
            foreach ($user->getAuthData() as $authData) {
                if ($authData instanceof TOTPAuth) {
                    $totpAuth = $authData;
                }

                if ($authData instanceof StoredPassword) {
                    $storedPassword = $authData;
                }
            }

            if (null === $storedPassword) {
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
                    $user = new GoogleAuthPasswordAuthenticatedUser(
                        $user,
                        $storedPassword,
                    );
                } else {
                    $user = new TOTPPasswordAuthenticatedUser(
                        $user,
                        $storedPassword,
                    );
                }

                return $user->setTOTPAuth($totpAuth);
            }

            return new PasswordAuthenticatedUser($user, $storedPassword);
        });

        $this->loader->fetch(
            new UserByEmailQuery($username),
            $promise,
        );

        $loadedUser = $promise->fetchResult();
        if (!$loadedUser instanceof AbstractPasswordAuthUser) {
            throw new UserNotFoundException();
        }

        return $loadedUser;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        if ($user instanceof AbstractPasswordAuthUser) {
            return $this->fetchUserByUsername($user->getUserIdentifier());
        }

        return null;
    }

    public function upgradePassword(
        PasswordAuthenticatedUserInterface|UserInterface $user,
        string $newHashedPassword
    ): void {
        if (!$user instanceof AbstractPasswordAuthUser) {
            return;
        }

        $storedPassword = $user->getWrappedStoredPassword();
        $storedPassword->setHashedPassword($newHashedPassword);
        $storedPassword->setAlgo(PasswordAuthenticatedUser::class);

        $this->userWriter->save($user->getWrappedUser());
    }

    /**
     * @param class-string<AbstractPasswordAuthUser> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        return (new ReflectionClass($class))->isSubclassOf(AbstractPasswordAuthUser::class);
    }
}

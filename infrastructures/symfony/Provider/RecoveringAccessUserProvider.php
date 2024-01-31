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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
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
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthUserWithRecoveryAccess;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPUserWithRecoveryAccess;
use Teknoo\East\CommonBundle\Object\UserWithRecoveryAccess;
use Teknoo\East\CommonBundle\Provider\Exception\MissingUserException;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Query\User\UserByEmailQuery;

use function interface_exists;

/**
 * Symfony user provider to load East Common's user authenticated thanks to a Recovery Access.
 * It can manage only UserWithRecoveryAccess, with or without TOTP access.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements UserProviderInterface<UserWithRecoveryAccess>
 */
class RecoveringAccessUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserLoader $loader,
        private readonly SymfonyUserWriter $userWriter,
        private readonly string $recoveryAccessRole,
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

    protected function fetchUserByUsername(string $username): UserWithRecoveryAccess
    {
        $role = $this->recoveryAccessRole;

        /** @var Promise<User, UserWithRecoveryAccess, mixed> $promise */
        $promise = new Promise(onSuccess: static function (User $user) use ($role): ?UserWithRecoveryAccess {
            $totpAuth = null;
            $accessAuth = null;

            foreach ($user->getAuthData() as $authData) {
                if ($authData instanceof TOTPAuth) {
                    $totpAuth = $authData;
                }

                if ($authData instanceof RecoveryAccess) {
                    $accessAuth = $authData;
                }
            }

            if (null === $accessAuth) {
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
                    $user = new GoogleAuthUserWithRecoveryAccess(
                        user: $user,
                        recoveryAccess: $accessAuth,
                        temporaryRole: $role,
                    );
                } else {
                    $user = new TOTPUserWithRecoveryAccess(
                        user: $user,
                        recoveryAccess: $accessAuth,
                        temporaryRole: $role,
                    );
                }

                return $user->setTOTPAuth($totpAuth);
            }

            return new UserWithRecoveryAccess(
                user: $user,
                recoveryAccess: $accessAuth,
                temporaryRole: $role,
            );
        });

        $this->loader->fetch(
            new UserByEmailQuery($username),
            $promise,
        );

        $loadedUser = $promise->fetchResult();
        if (!$loadedUser instanceof UserWithRecoveryAccess) {
            throw new UserNotFoundException();
        }

        return $loadedUser;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof UserWithRecoveryAccess) {
            return $this->fetchUserByUsername($user->getUserIdentifier());
        }

        throw new MissingUserException(
            "{$user->getUserIdentifier()} is not available with the provider " . self::class,
        );
    }

    public function upgradePassword(
        PasswordAuthenticatedUserInterface|UserInterface $user,
        string $newHashedPassword
    ): void {
        if (!$user instanceof UserWithRecoveryAccess) {
            return;
        }

        $wUser = $user->getWrappedUser();
        $storedPassword = new StoredPassword();
        $storedPassword->setHashedPassword($newHashedPassword);
        $storedPassword->setAlgo(PasswordAuthenticatedUser::class);
        $wUser->addAuthData($storedPassword);

        $this->userWriter->save($user->getWrappedUser());
    }

    /**
     * @param class-string<UserWithRecoveryAccess> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        return $class === UserWithRecoveryAccess::class
            || ((new ReflectionClass($class))->isSubclassOf(UserWithRecoveryAccess::class));
    }
}

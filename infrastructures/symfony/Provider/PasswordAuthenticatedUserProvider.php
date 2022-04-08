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

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Object\AbstractPasswordAuthUser;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\CommonBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony user provider to load East Common's user from email.
 * Use the standard User Loader, and wrap user fetched into a PasswordAuthenticatedUser or a LegacyUser.
 * A LegacyUser is returned when the user is authenticated with the legacy couple of salt+password hashed thanks to
 * pbkdf2.
 * A PasswordAuthenticatedUser is returned for all user authenticated thanks to a modern hash method like sodium.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class PasswordAuthenticatedUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private UserLoader $loader,
        private SymfonyUserWriter $userWriter,
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
        $promise = new Promise(static function (User $user) {
            foreach ($user->getAuthData() as $authData) {
                if (!$authData instanceof StoredPassword) {
                    continue;
                }

                return new PasswordAuthenticatedUser($user, $authData);
            }
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
        $reflection = new ReflectionClass($class);
        return $reflection->isSubclassOf(AbstractPasswordAuthUser::class);
    }
}

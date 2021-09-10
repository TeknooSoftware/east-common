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

namespace Teknoo\East\WebsiteBundle\Provider;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User;
use Teknoo\East\WebsiteBundle\Object\AbstractPasswordAuthUser;
use Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\LegacyUser;
use Teknoo\East\WebsiteBundle\Object\PasswordAuthenticatedUser;

/**
 * Symfony user provider to load East Website's user from email.
 * Use the standard User Loader, and wrap user fetched into a PasswordAuthenticatedUser or a LegacyUser.
 * A LegacyUser is returned when the user is authenticated with the legacy couple of salt+password hashed thanks to
 * pbkdf2.
 * A PasswordAuthenticatedUser is returned for all user authenticated thanks to a modern hash method like sodium.
 * The salt must be empty and the used algo stored into the StoredPassword instance.
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

    public function loadUserByIdentifier(string $username): ?UserInterface
    {
        return $this->fetchUserByUsername($username);
    }

    public function loadUserByUsername(string $username)
    {
        return $this->fetchUserByUsername($username);
    }

    protected function fetchUserByUsername(string $username): UserInterface
    {
        $this->loader->query(
            new UserByEmailQuery($username),
            $promise = new Promise(static function (User $user) {
                foreach ($user->getAuthData() as $authData) {
                    if (!$authData instanceof StoredPassword) {
                        continue;
                    }

                    if (!empty($authData->getSalt())) {
                        return new LegacyUser($user, $authData);
                    }

                    return new PasswordAuthenticatedUser($user, $authData);
                }
            })
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
            return $this->fetchUserByUsername($user->getUsername());
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
        $storedPassword->setSalt('');
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

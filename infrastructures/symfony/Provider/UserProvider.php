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
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\LegacyUser;
use Teknoo\East\WebsiteBundle\Object\User;

use function interface_exists;

/**
 * Symfony user provider to load East Website's user from email.
 * Use the standard User Loader, and wrap user fetched into User or LegacyUser, available in this namespace.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class UserProvider
{
    public function __construct(
        private UserLoader $loader
    ) {
    }

    public function loadUserByIdentifier(string $username): ?UserInterface
    {
        return $this->fetchUserByUsername($username);
    }

    protected function fetchUserByUsername(string $username): UserInterface
    {
        $loadedUser = null;
        $this->loader->query(
            new UserByEmailQuery($username),
            new Promise(static function ($user) use (&$loadedUser) {
                if (interface_exists(LegacyPasswordAuthenticatedUserInterface::class)) {
                    $loadedUser = new LegacyUser($user);
                } else {
                    $loadedUser = new User($user);
                }
            })
        );

        if (!$loadedUser) {
            throw new UsernameNotFoundException(); //todo deprecated
        }

        return $loadedUser;
    }

    public function refreshUser(UserInterface $user): ?UserInterface
    {
        if ($user instanceof User) {
            return $this->fetchUserByUsername($user->getUsername());
        }

        return null;
    }

    /**
     * @param class-string<User> $class
     * @throws ReflectionException
     */
    public function supportsClass($class): bool
    {
        $reflection = new ReflectionClass($class);
        return $class === User::class || $reflection->isSubclassOf(User::class);
    }
}

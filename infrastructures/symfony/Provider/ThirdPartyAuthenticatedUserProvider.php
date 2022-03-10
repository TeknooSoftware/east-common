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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Provider;

use ReflectionException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\East\Website\Object\User;
use Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Query\User\UserByEmailQuery;

/**
 * Symfony user provider to load East Website's user authenticated thanks to OAuth2Authenticator, or any third party
 * authenticated. It can manage only ThirdPartyAuthenticatedUser.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ThirdPartyAuthenticatedUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserLoader $loader,
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
        $promise = new Promise(static function (User $user) {
            foreach ($user->getAuthData() as $authData) {
                if (!$authData instanceof ThirdPartyAuth) {
                    continue;
                }

                return new ThirdPartyAuthenticatedUser($user, $authData);
            }
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

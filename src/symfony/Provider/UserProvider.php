<?php

declare(strict_types=1);

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\WebsiteBundle\Provider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Query\User\UserByEmailQuery;
use Teknoo\East\WebsiteBundle\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserLoader
     */
    private $loader;

    /**
     * UserProvider constructor.
     * @param UserLoader $loader
     */
    public function __construct(UserLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username): ?UserInterface
    {
        $loadedUser = null;
        $this->loader->query(
            new UserByEmailQuery($username),
            new Promise(function ($user) use (&$loadedUser) {
                $loadedUser = new User($user);
            })
        );

        return $loadedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): ?UserInterface
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     * @throws \ReflectionException
     */
    public function supportsClass($class): bool
    {
        $reflection = new \ReflectionClass($class);
        return $class === User::class || $reflection->isSubclassOf(User::class);
    }
}

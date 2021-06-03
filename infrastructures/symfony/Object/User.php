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

namespace Teknoo\East\WebsiteBundle\Object;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Website\Object\User as BaseUser;

use function interface_exists;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class User implements UserInterface, EquatableInterface
{
    public function __construct(
        private BaseUser $user
    ) {
        if (interface_exists(LegacyPasswordAuthenticatedUserInterface::class) && !$this instanceof LegacyUser) {
            trigger_deprecation(
                'teknoo/east-website',
                '5.0',
                'class "%s()" is deprecated, use \Teknoo\East\WebsiteBundle\Object\LegacyUser instead.',
                __CLASS__
            );
        }
    }

    public function getRoles()
    {
        return $this->user->getRoles();
    }

    public function getPassword(): string
    {
        return $this->user->getPassword();
    }

    public function getSalt()
    {
        return $this->user->getSalt();
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getUsername();
    }

    public function eraseCredentials(): self
    {
        $this->user->eraseCredentials();

        return $this;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self &&  $user->getUsername() === $this->getUsername();
    }
}

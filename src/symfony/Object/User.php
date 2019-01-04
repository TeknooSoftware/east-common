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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\WebsiteBundle\Object;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class User implements UserInterface, EquatableInterface
{
    /**
     * @var BaseUser
     */
    private $user;

    /**
     * User constructor.
     * @param BaseUser $user
     */
    public function __construct(BaseUser $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->user->getRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->user->getPassword();
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->user->getSalt();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->user->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->user->eraseCredentials();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user): bool
    {
        return $user->getUsername() === $this->getUsername();
    }
}

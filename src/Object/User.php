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

namespace Teknoo\East\Common\Object;

use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Contracts\User\UserInterface;

use function is_array;
use function iterator_to_array;
use function trim;

/**
 * Class to defined persisted user allow to be connected to the website. An user can have some roles, like admin,
 * redactor, or simple user.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class User implements IdentifiedObjectInterface, UserInterface, DeletableInterface, TimestampableInterface
{
    use ObjectTrait;

    private string $firstName = '';

    private string $lastName = '';

    /**
     * @var string[]
     */
    private iterable $roles = [];

    private string $email = '';

    private bool $active = true;

    /**
     * @var iterable<AuthDataInterface>
     */
    private iterable $authData = [];

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function __toString()
    {
        return trim($this->getFirstName() . ' ' . $this->getLastName());
    }

    /**
     * @return iterable<string>
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @param iterable<string> $roles
     */
    public function setRoles(iterable $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param iterable<AuthDataInterface> $authData
     */
    public function setAuthData(iterable $authData): User
    {
        $this->authData = $authData;

        return $this;
    }

    public function addAuthData(AuthDataInterface $authData): User
    {
        if (!is_array($this->authData)) {
            $this->authData = iterator_to_array($this->authData);
        }

        $this->authData[] = $authData;

        return $this;
    }

    public function getAuthData(): iterable
    {
        return $this->authData;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): User
    {
        $this->active = $active;

        return $this;
    }
}

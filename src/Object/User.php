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

namespace Teknoo\East\Common\Object;

use Stringable;
use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\TimestampableInterface;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;
use Teknoo\East\Common\Contracts\User\UserInterface;

use function array_values;
use function is_array;
use function iterator_to_array;
use function trim;

/**
 * Class to defined persisted user allow to be connected to the website. An user can have some roles, like admin,
 * redactor, or simple user.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class User implements IdentifiedObjectInterface, UserInterface, DeletableInterface, TimestampableInterface, Stringable
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

    public function __toString(): string
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
        $set = [];
        foreach ($authData as $instance) {
            if (!isset($set[$instance::class])) {
                $set[$instance::class] = $instance;
            }
        }

        $this->authData = array_values($set);

        return $this;
    }

    public function addAuthData(AuthDataInterface $authData): User
    {
        $set = $this->authData;
        if (!is_array($set)) {
            $set = iterator_to_array($set);
        }

        $set[] = $authData;

        return $this->setAuthData($set);
    }

    public function getAuthData(): iterable
    {
        return $this->authData;
    }

    public function getOneAuthData(string $className): ?AuthDataInterface
    {
        foreach ($this->getAuthData() as $authData) {
            if ($className === $authData::class) {
                return $authData;
            }
        }

        return null;
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

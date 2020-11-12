<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Object;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class User implements ObjectInterface, DeletableInterface, TimestampableInterface
{
    use ObjectTrait;

    private string $firstName = '';

    private string $lastName = '';

    /**
     * @var string[]
     */
    private array $roles = [];

    private string $email = '';

    private ?string $password = null;

    private ?string $originalPassword = null;

    private string $salt = '';

    public function __construct()
    {
        //initialize for new user
        $this->salt = \sha1(\uniqid('', true));
    }

    public function getFirstName(): string
    {
        return (string) $this->firstName;
    }

    public function setFirstName(string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return (string) $this->lastName;
    }

    public function setLastName(string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function __toString()
    {
        return \trim($this->getFirstName() . ' ' . $this->getLastName());
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        if (empty($this->originalPassword)) {
            $this->originalPassword = $this->password;
        }

        return (string) $this->password;
    }

    public function getOriginalPassword(): string
    {
        return (string) $this->originalPassword;
    }

    public function hasUpdatedPassword(): bool
    {
        $originalPwd = $this->getOriginalPassword();
        $pwd = $this->getPassword();

        return empty($originalPwd) && !empty($pwd) || ($originalPwd != $pwd);
    }

    public function setPassword(?string $password): User
    {
        if (empty($this->originalPassword)) {
            $this->originalPassword = $this->password;
        }

        $this->password = $password;

        return $this;
    }

    public function getSalt(): string
    {
        return (string) $this->salt;
    }

    public function setSalt(string $salt): User
    {
        $this->salt = $salt;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function eraseCredentials(): self
    {
        $this->password = '';
        $this->originalPassword = '';

        return $this;
    }
}

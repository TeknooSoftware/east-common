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

namespace Teknoo\East\Website\Object;

use Teknoo\East\Website\Contracts\User\AuthDataInterface;

/**
 * Class to defined persisted user's password to authenticate it on a website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class StoredPassword implements AuthDataInterface
{
    private string $type = self::class;

    private ?string $algo = '';

    private bool $unhashedPassword = false;

    private ?string $hash = null;

    public function getAlgo(): ?string
    {
        return $this->algo;
    }

    public function setAlgo(string $algo): self
    {
        $this->algo = $algo;

        return $this;
    }

    public function getHash(): string
    {
        return (string) $this->hash;
    }

    public function getPassword(): string
    {
        return $this->getHash();
    }

    /*
     * Empty passwords are not allowed and ignored.
     * To clear a password, you must pass an empty hash to setHashedPassword
     * A hash must not be rehashed, be a password must always be hashed before persisted
     */
    public function setPassword(?string $password): self
    {
        if (empty($password)) {
            return $this;
        }

        $this->unhashedPassword = true;
        $this->hash = $password;

        return $this;
    }

    public function setHashedPassword(?string $hashedPassword): self
    {
        $this->unhashedPassword = false;
        $this->hash = $hashedPassword;

        return $this;
    }

    public function mustHashPassword(): bool
    {
        return $this->unhashedPassword;
    }

    public function eraseCredentials(): self
    {
        $this->hash = '';

        return $this;
    }
}

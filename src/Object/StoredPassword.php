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

use Teknoo\East\Common\Contracts\User\AuthDataInterface;

/**
 * Class to defined persisted user's password to authenticate it on a website
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class StoredPassword implements AuthDataInterface
{
    private string $type = self::class;

    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private ?string $algo = '';

    private bool $unhashedPassword = false;

    private ?string $hash = null;

    public function __construct(
        ?string $algo = '',
        bool $unhashedPassword = false,
        ?string $hash = null,
    ) {
        $this->algo = $algo;
        $this->unhashedPassword = $unhashedPassword;
        $this->hash = $hash;
    }

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

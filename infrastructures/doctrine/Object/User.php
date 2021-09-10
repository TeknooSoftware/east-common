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

namespace Teknoo\East\Website\Doctrine\Object;

use Teknoo\East\Website\Object\StoredPassword;
use Teknoo\East\Website\Object\User as BaseUser;

/**
 * Internal child class of East Website User class to manage salt and hash migration into a embedded StoredPassword
 * instance.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
* @internal
 */
class User extends BaseUser
{
    private ?string $legacySalt = null;

    private ?string $legacyHash = null;

    private function getStoredPassword(): StoredPassword
    {
        $storedPassword = null;
        foreach ($this->getAuthData() as $authData) {
            if ($authData instanceof StoredPassword) {
                $storedPassword = $authData;

                break;
            }
        }

        if (null === $storedPassword) {
            $storedPassword = new StoredPassword();

            $this->addAuthData($storedPassword);
        }

        return $storedPassword;
    }

    public function postLoad(): self
    {
        if (empty($this->legacySalt) && empty($this->legacyHash)) {
            return $this;
        }

        $storedPassword = $this->getStoredPassword();

        if (!empty($storedPassword->getAlgo())) {
            return $this;
        }

        if (
            !empty($this->legacySalt)
            && empty($storedPassword->getSalt())
        ) {
            $storedPassword->setSalt($this->legacySalt);
        }

        if (
            !empty($this->legacyHash)
            && empty($storedPassword->getHash())
        ) {
            $storedPassword->setHashedPassword($this->legacyHash);
        }

        $this->legacyHash = null;
        $this->legacySalt = null;

        return $this;
    }

    public function migrateSalt(string $salt): self
    {
        $this->legacySalt = $salt;

        return $this;
    }

    public function migrateHash(string $hash): self
    {
        $this->legacyHash = $hash;

        return $this;
    }
}

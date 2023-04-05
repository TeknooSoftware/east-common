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

namespace Teknoo\East\CommonBundle\Object;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * Abstract Symfony user implentation to wrap a East Common user instance authenticated via a password.
 * Authenticating data are stored into a StoredPassword instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPasswordAuthUser extends AbstractUser implements PasswordAuthenticatedUserInterface
{
    public function __construct(
        BaseUser $user,
        protected StoredPassword $password,
    ) {
        parent::__construct($user);
    }

    public function getPassword(): string
    {
        return $this->password->getHash();
    }

    public function getWrappedStoredPassword(): StoredPassword
    {
        return $this->password;
    }

    public function eraseCredentials(): self
    {
        $this->password->eraseCredentials();

        return $this;
    }
}

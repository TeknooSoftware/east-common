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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Object;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Object\User as BaseUser;

use function hash;
use function is_array;
use function iterator_to_array;

/**
 * Abstract Symfony user implentation to wrap a East Common user instance
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractUser implements
    UserInterface,
    EquatableInterface
{
    public function __construct(
        private readonly BaseUser $user,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->user->getRoles();
        if (!is_array($roles)) {
            $roles = iterator_to_array($roles);
        }

        return $roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->user->getUserIdentifier();
    }

    public function getEmail(): string
    {
        return $this->user->getEmail();
    }

    public function getHash(): string
    {
        return hash('sha256', $this->getEmail());
    }

    public function getWrappedUser(): User
    {
        return $this->user;
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self &&  $user->getUserIdentifier() === $this->getUserIdentifier();
    }
}

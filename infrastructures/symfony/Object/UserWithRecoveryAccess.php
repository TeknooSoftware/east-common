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

use Symfony\Component\Security\Core\User\UserInterface;
use Teknoo\East\Common\Object\RecoveryAccess;
use Teknoo\East\Common\Object\User as BaseUser;

use function hash;

/**
 * Symfony user implentation to wrap a East Common user instance authenticated via a recovery access.
 * Recovering access data are stored into a RecoveryAccess instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class UserWithRecoveryAccess extends AbstractUser
{
    public function __construct(
        BaseUser $user,
        private RecoveryAccess $recoveryAccess,
        private readonly string $temporaryRole,
    ) {
        parent::__construct($user);
    }

    public function getRoles(): array
    {
        return [
            $this->temporaryRole,
        ];
    }

    public function getToken(): string
    {
        return $this->recoveryAccess->getParams()['token'] ?? '';
    }

    public function getHash(): string
    {
        return hash('sha256', $this->getEmail() . ':' . $this->getToken());
    }

    public function eraseCredentials(): void
    {
        $this->getWrappedUser()->removeAuthData(RecoveryAccess::class);
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user instanceof self &&  $user->getUserIdentifier() === $this->getUserIdentifier();
    }
}

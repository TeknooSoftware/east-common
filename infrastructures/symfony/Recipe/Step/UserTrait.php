<?php

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Trait to fetch Symfony User from token storage
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait UserTrait
{
    private function getUser(): ?UserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!($user = $token->getUser()) instanceof UserInterface) {
            return null;
        }

        return $user;
    }
}

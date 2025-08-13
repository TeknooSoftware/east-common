<?php

declare(strict_types=1);

namespace Symfony\Component\Security\Core\User;

if (!\interface_exists(\Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface::class, false)) {
    interface LegacyPasswordAuthenticatedUserInterface
    {
    }
}

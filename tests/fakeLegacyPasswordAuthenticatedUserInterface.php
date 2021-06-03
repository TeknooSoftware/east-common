<?php

namespace Symfony\Component\Security\Core\User;

if (!\interface_exists('Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface', false)) {
    interface LegacyPasswordAuthenticatedUserInterface
    {
    }
}

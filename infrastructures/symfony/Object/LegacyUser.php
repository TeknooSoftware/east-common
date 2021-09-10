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

namespace Teknoo\East\WebsiteBundle\Object;

use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

/**
 * Symfony user implentation to wrap a East Website user instance authenticated via a password hashed by the legacy
 * pbkdf2 algo, with a salt. Authenticating data are stored into a StoredPassword instance.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LegacyUser extends AbstractPasswordAuthUser implements LegacyPasswordAuthenticatedUserInterface
{
    public function getSalt(): ?string
    {
        return $this->password->getSalt();
    }
}

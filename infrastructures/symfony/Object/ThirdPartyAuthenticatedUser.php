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

namespace Teknoo\East\CommonBundle\Object;

use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\User as BaseUser;

/**
 * Symfony user implentation to wrap a East Common user instance authenticated via a third party, like
 * OAuth2Authenticator. Authenticating data are stored into a ThirdPartyAuth instance.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ThirdPartyAuthenticatedUser extends AbstractUser
{
    public function __construct(
        BaseUser $user,
        protected ThirdPartyAuth $auth,
    ) {
        parent::__construct($user);
    }

    public function getWrappedThirdAuth(): ThirdPartyAuth
    {
        return $this->auth;
    }

    public function getPassword(): ?string
    {
        return $this->auth->getToken();
    }

    public function eraseCredentials(): self
    {
        return $this;
    }
}

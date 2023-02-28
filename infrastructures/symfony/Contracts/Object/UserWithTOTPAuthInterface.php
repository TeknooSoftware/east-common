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
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Contracts\Object;

use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;

/**
 * Contact defining Symfony users classes able to support an 2FA authentication with TOTP protocoles
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface UserWithTOTPAuthInterface
{
    public function setTOTPAuth(?TOTPAuth $TOTPAuth): UserWithTOTPAuthInterface;

    public function getTOTPAuth(): ?TOTPAuth;

    public function getWrappedUser(): User;
}

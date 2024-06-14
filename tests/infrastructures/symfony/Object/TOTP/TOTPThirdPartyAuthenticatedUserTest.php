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

namespace Teknoo\Tests\East\CommonBundle\Object\TOTP;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Contracts\Object\UserWithTOTPAuthInterface;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPAuthTrait;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPThirdPartyAuthenticatedUser;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversTrait(TOTPAuthTrait::class)]
#[CoversClass(TOTPThirdPartyAuthenticatedUser::class)]
class TOTPThirdPartyAuthenticatedUserTest extends AbstractTOTPAuthTests
{
    protected function buildUser(): UserWithTOTPAuthInterface
    {
        return new TOTPThirdPartyAuthenticatedUser(
            $this->createMock(User::class),
            $this->createMock(ThirdPartyAuth::class),
        );
    }
}
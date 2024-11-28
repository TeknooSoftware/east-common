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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Recipe\Plan\Disable2FA;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Disable2FA::class)]
class Disable2FATest extends TestCase
{
    use BasePlanTestTrait;

    public function buildPlan(): Disable2FA
    {
        return new Disable2FA(
            $this->createMock(OriginalRecipeInterface::class),
            $this->createMock(DisableTOTP::class),
            $this->createMock(RedirectClientInterface::class),
            $this->createMock(RenderError::class),
            'foo',
        );
    }
}

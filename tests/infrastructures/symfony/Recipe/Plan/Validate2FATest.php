<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Recipe\Plan\Validate2FA;
use Teknoo\East\CommonBundle\Recipe\Step\ValidateTOTP;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Validate2FA::class)]
class Validate2FATest extends TestCase
{
    use BasePlanTestTrait;

    public function buildPlan(): Validate2FA
    {
        return new Validate2FA(
            $this->createStub(OriginalRecipeInterface::class),
            $this->createStub(CreateObject::class),
            $this->createStub(FormHandlingInterface::class),
            $this->createStub(FormProcessingInterface::class),
            $this->createStub(ValidateTOTP::class),
            $this->createStub(RedirectClientInterface::class),
            $this->createStub(RenderFormInterface::class),
            $this->createStub(RenderError::class),
            'foo',
        );
    }
}

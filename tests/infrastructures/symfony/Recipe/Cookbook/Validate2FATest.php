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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Cookbook;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Validate2FA;
use Teknoo\East\CommonBundle\Recipe\Step\ValidateTOTP;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers      \Teknoo\East\CommonBundle\Recipe\Cookbook\Validate2FA
 */
class Validate2FATest extends TestCase
{
    use BaseCookbookTestTrait;

    public function buildCookbook(): Validate2FA
    {
        return new Validate2FA(
            $this->createMock(OriginalRecipeInterface::class),
            $this->createMock(CreateObject::class),
            $this->createMock(FormHandlingInterface::class),
            $this->createMock(FormProcessingInterface::class),
            $this->createMock(ValidateTOTP::class),
            $this->createMock(RedirectClientInterface::class),
            $this->createMock(RenderFormInterface::class),
            $this->createMock(RenderError::class),
            'foo',
        );
    }
}

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

namespace Teknoo\Tests\East\CommonBundle\Recipe\Cookbook;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Recipe\Cookbook\Enable2FA;
use Teknoo\East\CommonBundle\Recipe\Step\EnableTOTP;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers      \Teknoo\East\CommonBundle\Recipe\Cookbook\Enable2FA
 */
class Enable2FATest extends TestCase
{
    use BaseCookbookTestTrait;

    public function buildCookbook(): Enable2FA
    {
        return new Enable2FA(
            $this->createMock(OriginalRecipeInterface::class),
            $this->createMock(EnableTOTP::class),
            $this->createMock(CreateObject::class),
            $this->createMock(FormHandlingInterface::class),
            $this->createMock(RenderFormInterface::class),
            $this->createMock(RenderError::class),
            'foo',
        );
    }
}

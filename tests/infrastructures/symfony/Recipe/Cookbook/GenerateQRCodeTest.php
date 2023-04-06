<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Recipe\Cookbook\GenerateQRCode;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\Recipe\RecipeInterface as OriginalRecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers      \Teknoo\East\CommonBundle\Recipe\Cookbook\GenerateQRCode
 */
class GenerateQRCodeTest extends TestCase
{
    use BaseCookbookTestTrait;

    public function buildCookbook(): GenerateQRCode
    {
        return new GenerateQRCode(
            $this->createMock(OriginalRecipeInterface::class),
            $this->createMock(GenerateQRCodeTextForTOTP::class),
            $this->createMock(BuildQrCodeInterface::class),
            $this->createMock(RenderError::class),
            'foo',
        );
    }
}

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

namespace Teknoo\Tests\East\Common\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Recipe\Plan\RenderMediaEndPoint;
use Teknoo\East\Common\Recipe\Step\LoadMedia;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SendMedia;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RenderMediaEndPoint::class)]
class RenderMediaEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?LoadMedia $loadMedia = null;

    private ?GetStreamFromMediaInterface $getStreamFromMedia = null;

    private ?SendMedia $sendMedia = null;

    private ?RenderError $renderError = null;

    /**
     * @return RecipeInterface|MockObject
     */
    public function getRecipe(): RecipeInterface
    {
        if (null === $this->recipe) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return LoadMedia|MockObject
     */
    public function getLoadMedia(): LoadMedia
    {
        if (null === $this->loadMedia) {
            $this->loadMedia = $this->createStub(LoadMedia::class);
        }

        return $this->loadMedia;
    }

    /**
     * @return GetStreamFromMediaInterface|MockObject
     */
    public function getGetStreamFromMedia(): GetStreamFromMediaInterface
    {
        if (null === $this->getStreamFromMedia) {
            $this->getStreamFromMedia = $this->createStub(GetStreamFromMediaInterface::class);
        }

        return $this->getStreamFromMedia;
    }

    /**
     * @return SendMedia|MockObject
     */
    public function getSendMedia(): SendMedia
    {
        if (null === $this->sendMedia) {
            $this->sendMedia = $this->createStub(SendMedia::class);
        }

        return $this->sendMedia;
    }

    /**
     * @return RenderError|MockObject
     */
    public function getRenderError(): RenderError
    {
        if (null === $this->renderError) {
            $this->renderError = $this->createStub(RenderError::class);
        }

        return $this->renderError;
    }

    public function buildPlan(): RenderMediaEndPoint
    {
        return new RenderMediaEndPoint(
            $this->getRecipe(),
            $this->getLoadMedia(),
            $this->getGetStreamFromMedia(),
            $this->getSendMedia(),
            $this->getRenderError()
        );
    }
}

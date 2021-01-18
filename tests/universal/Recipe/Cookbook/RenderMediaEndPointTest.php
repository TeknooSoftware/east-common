<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
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

namespace Teknoo\Tests\East\Website\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\Recipe\Cookbook\RenderMediaEndPoint;
use Teknoo\East\Website\Recipe\Step\LoadMedia;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\SendMedia;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Cookbook\RenderMediaEndPoint
 */
class RenderMediaEndPointTest extends TestCase
{
    use BaseCookbookTestTrait;

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
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return LoadMedia|MockObject
     */
    public function getLoadMedia(): LoadMedia
    {
        if (null === $this->loadMedia) {
            $this->loadMedia = $this->createMock(LoadMedia::class);
        }

        return $this->loadMedia;
    }

    /**
     * @return GetStreamFromMediaInterface|MockObject
     */
    public function getGetStreamFromMedia(): GetStreamFromMediaInterface
    {
        if (null === $this->getStreamFromMedia) {
            $this->getStreamFromMedia = $this->createMock(GetStreamFromMediaInterface::class);
        }

        return $this->getStreamFromMedia;
    }

    /**
     * @return SendMedia|MockObject
     */
    public function getSendMedia(): SendMedia
    {
        if (null === $this->sendMedia) {
            $this->sendMedia = $this->createMock(SendMedia::class);
        }

        return $this->sendMedia;
    }

    /**
     * @return RenderError|MockObject
     */
    public function getRenderError(): RenderError
    {
        if (null === $this->renderError) {
            $this->renderError = $this->createMock(RenderError::class);
        }

        return $this->renderError;
    }

    public function buildCookbook(): RenderMediaEndPoint
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

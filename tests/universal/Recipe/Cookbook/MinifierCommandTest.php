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

namespace Teknoo\Tests\East\Common\Recipe\Cookbook;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Cookbook\MinifierCommand;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MinifierCommand::class)]
class MinifierCommandTest extends TestCase
{
    use BaseCookbookTestTrait;

    private ?RecipeInterface $recipe = null;
    private ?LoadSource $loadSource = null;

    private ?MinifyAssets $minifyAssets = null;

    private ?PersistAsset $persistAsset = null;

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
     * @return LoadSource|MockObject
     */
    public function getLoadSource(): LoadSource
    {
        if (null === $this->loadSource) {
            $this->loadSource = $this->createMock(LoadSource::class);
        }

        return $this->loadSource;
    }

    /**
     * @return MinifyAssets|MockObject
     */
    public function getMinifyAssets(): MinifyAssets
    {
        if (null === $this->minifyAssets) {
            $this->minifyAssets = $this->createMock(MinifyAssets::class);
        }

        return $this->minifyAssets;
    }

    /**
     * @return PersistAsset|MockObject
     */
    public function getPersistAsset(): PersistAsset
    {
        if (null === $this->persistAsset) {
            $this->persistAsset = $this->createMock(PersistAsset::class);
        }

        return $this->persistAsset;
    }

    public function buildCookbook(): MinifierCommand
    {
        return new MinifierCommand(
            $this->getRecipe(),
            $this->getLoadSource(),
            $this->getMinifyAssets(),
            $this->getPersistAsset(),
        );
    }
}

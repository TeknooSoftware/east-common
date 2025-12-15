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
use Teknoo\East\Common\Recipe\Plan\MinifierCommand;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MinifierCommand::class)]
class MinifierCommandTest extends TestCase
{
    use BasePlanTestTrait;

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
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return LoadSource|MockObject
     */
    public function getLoadSource(): LoadSource
    {
        if (null === $this->loadSource) {
            $this->loadSource = $this->createStub(LoadSource::class);
        }

        return $this->loadSource;
    }

    /**
     * @return MinifyAssets|MockObject
     */
    public function getMinifyAssets(): MinifyAssets
    {
        if (null === $this->minifyAssets) {
            $this->minifyAssets = $this->createStub(MinifyAssets::class);
        }

        return $this->minifyAssets;
    }

    /**
     * @return PersistAsset|MockObject
     */
    public function getPersistAsset(): PersistAsset
    {
        if (null === $this->persistAsset) {
            $this->persistAsset = $this->createStub(PersistAsset::class);
        }

        return $this->persistAsset;
    }

    public function buildPlan(): MinifierCommand
    {
        return new MinifierCommand(
            $this->getRecipe(),
            $this->getLoadSource(),
            $this->getMinifyAssets(),
            $this->getPersistAsset(),
        );
    }
}

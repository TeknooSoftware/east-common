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

namespace Teknoo\Tests\East\Common\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Recipe\Plan\MinifierEndPoint;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ComputePath;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadPersistedAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ReturnFile;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(MinifierEndPoint::class)]
class MinifierEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?ComputePath $computePath = null;

    private ?LoadPersistedAsset $loadPersistedAsset = null;

    private ?JumpIf $jumpIf = null;

    private ?LoadSource $loadSource = null;

    private ?MinifyAssets $minifyAssets = null;

    private ?PersistAsset $persistAsset = null;

    private ?ReturnFile $returnFile = null;

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
     * @return ComputePath|MockObject
     */
    public function getComputePath(): ComputePath
    {
        if (null === $this->computePath) {
            $this->computePath = $this->createMock(ComputePath::class);
        }

        return $this->computePath;
    }

    /**
     * @return LoadPersistedAsset|MockObject
     */
    public function getLoadPersistedAsset(): LoadPersistedAsset
    {
        if (null === $this->loadPersistedAsset) {
            $this->loadPersistedAsset = $this->createMock(LoadPersistedAsset::class);
        }

        return $this->loadPersistedAsset;
    }

    /**
     * @return JumpIf|MockObject
     */
    public function getJumpIf(): JumpIf
    {
        if (null === $this->jumpIf) {
            $this->jumpIf = $this->createMock(JumpIf::class);
        }

        return $this->jumpIf;
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

    /**
     * @return ReturnFile|MockObject
     */
    public function getReturnFile(): ReturnFile
    {
        if (null === $this->returnFile) {
            $this->returnFile = $this->createMock(ReturnFile::class);
        }

        return $this->returnFile;
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

    public function buildPlan(): MinifierEndPoint
    {
        return new MinifierEndPoint(
            $this->getRecipe(),
            $this->getComputePath(),
            $this->getLoadPersistedAsset(),
            $this->getJumpIf(),
            $this->getLoadSource(),
            $this->getMinifyAssets(),
            $this->getPersistAsset(),
            $this->getReturnFile(),
            $this->getRenderError(),
        );
    }
}

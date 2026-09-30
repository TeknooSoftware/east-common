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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\User\JwtCreateTokenInterface;
use Teknoo\East\Common\Recipe\Plan\CreateJwtTokenEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CreateJwtTokenEndPoint::class)]
class CreateJwtTokenEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private (RecipeInterface&Stub)|null $recipe = null;

    private (CreateObject&Stub)|null $createObject = null;

    private (JwtCreateTokenInterface&Stub)|null $jwtCreateToken = null;

    private (Render&Stub)|null $render = null;

    private (RenderError&Stub)|null $renderError = null;

    public function getRecipe(): RecipeInterface&Stub
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getCreateObject(): CreateObject&Stub
    {
        if (!$this->createObject instanceof CreateObject) {
            $this->createObject = $this->createStub(CreateObject::class);
        }

        return $this->createObject;
    }

    public function getJwtCreateToken(): JwtCreateTokenInterface&Stub
    {
        if (!$this->jwtCreateToken instanceof JwtCreateTokenInterface) {
            $this->jwtCreateToken = $this->createStub(JwtCreateTokenInterface::class);
        }

        return $this->jwtCreateToken;
    }

    public function getRender(): Render&Stub
    {
        if (!$this->render instanceof Render) {
            $this->render = $this->createStub(Render::class);
        }

        return $this->render;
    }

    public function getRenderError(): RenderError&Stub
    {
        if (!$this->renderError instanceof RenderError) {
            $this->renderError = $this->createStub(RenderError::class);
        }

        return $this->renderError;
    }

    public function buildPlan(): CreateJwtTokenEndPoint
    {
        return new CreateJwtTokenEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getJwtCreateToken(),
            $this->getRender(),
            $this->getRenderError(),
            'foo.template',
        );
    }

    public function testTrainWithoutDefaultErrorTemplate(): void
    {
        $plan = new CreateJwtTokenEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getJwtCreateToken(),
            $this->getRender(),
            $this->getRenderError(),
        );

        $this->assertInstanceOf(
            CreateJwtTokenEndPoint::class,
            $plan->train($this->createStub(ChefInterface::class)),
        );
    }
}

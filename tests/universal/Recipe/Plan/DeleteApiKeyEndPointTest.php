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
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\LoadCurrentUserInterface;
use Teknoo\East\Common\Recipe\Plan\DeleteApiKeyEndPoint;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\User\RemoveApiKey;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteApiKeyEndPoint::class)]
class DeleteApiKeyEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private (RecipeInterface&Stub)|null $recipe = null;

    private (LoadCurrentUserInterface&Stub)|null $loadCurrentUser = null;

    private (RemoveApiKey&Stub)|null $removeApiKey = null;

    private (SaveObject&Stub)|null $saveObject = null;

    private (RedirectClientInterface&Stub)|null $redirectClient = null;

    private (RenderError&Stub)|null $renderError = null;

    public function getRecipe(): RecipeInterface&Stub
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getLoadCurrentUser(): LoadCurrentUserInterface&Stub
    {
        if (!$this->loadCurrentUser instanceof LoadCurrentUserInterface) {
            $this->loadCurrentUser = $this->createStub(LoadCurrentUserInterface::class);
        }

        return $this->loadCurrentUser;
    }

    public function getRemoveApiKey(): RemoveApiKey&Stub
    {
        if (!$this->removeApiKey instanceof RemoveApiKey) {
            $this->removeApiKey = $this->createStub(RemoveApiKey::class);
        }

        return $this->removeApiKey;
    }

    public function getSaveObject(): SaveObject&Stub
    {
        if (!$this->saveObject instanceof SaveObject) {
            $this->saveObject = $this->createStub(SaveObject::class);
        }

        return $this->saveObject;
    }

    public function getRedirectClient(): RedirectClientInterface&Stub
    {
        if (!$this->redirectClient instanceof RedirectClientInterface) {
            $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        }

        return $this->redirectClient;
    }

    public function getRenderError(): RenderError&Stub
    {
        if (!$this->renderError instanceof RenderError) {
            $this->renderError = $this->createStub(RenderError::class);
        }

        return $this->renderError;
    }

    public function buildPlan(): DeleteApiKeyEndPoint
    {
        return new DeleteApiKeyEndPoint(
            $this->getRecipe(),
            $this->getLoadCurrentUser(),
            $this->getRemoveApiKey(),
            $this->getSaveObject(),
            $this->getRedirectClient(),
            $this->getRenderError(),
            'foo.template',
        );
    }

    public function testTrainWithoutDefaultErrorTemplate(): void
    {
        $plan = new DeleteApiKeyEndPoint(
            $this->getRecipe(),
            $this->getLoadCurrentUser(),
            $this->getRemoveApiKey(),
            $this->getSaveObject(),
            $this->getRedirectClient(),
            $this->getRenderError(),
        );

        $this->assertInstanceOf(
            DeleteApiKeyEndPoint::class,
            $plan->train($this->createStub(ChefInterface::class)),
        );
    }
}

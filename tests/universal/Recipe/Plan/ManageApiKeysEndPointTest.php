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
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\LoadCurrentUserInterface;
use Teknoo\East\Common\Recipe\Plan\ManageApiKeysEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ManageApiKeysEndPoint::class)]
class ManageApiKeysEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private (RecipeInterface&Stub)|null $recipe = null;

    private (CreateObject&Stub)|null $createObject = null;

    private (LoadCurrentUserInterface&Stub)|null $loadCurrentUser = null;

    private (FormHandlingInterface&Stub)|null $formHandling = null;

    private (FormProcessingInterface&Stub)|null $formProcessing = null;

    private (SaveObject&Stub)|null $saveObject = null;

    private (RenderFormInterface&Stub)|null $renderForm = null;

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

    public function getLoadCurrentUser(): LoadCurrentUserInterface&Stub
    {
        if (!$this->loadCurrentUser instanceof LoadCurrentUserInterface) {
            $this->loadCurrentUser = $this->createStub(LoadCurrentUserInterface::class);
        }

        return $this->loadCurrentUser;
    }

    public function getFormHandling(): FormHandlingInterface&Stub
    {
        if (!$this->formHandling instanceof FormHandlingInterface) {
            $this->formHandling = $this->createStub(FormHandlingInterface::class);
        }

        return $this->formHandling;
    }

    public function getFormProcessing(): FormProcessingInterface&Stub
    {
        if (!$this->formProcessing instanceof FormProcessingInterface) {
            $this->formProcessing = $this->createStub(FormProcessingInterface::class);
        }

        return $this->formProcessing;
    }

    public function getSaveObject(): SaveObject&Stub
    {
        if (!$this->saveObject instanceof SaveObject) {
            $this->saveObject = $this->createStub(SaveObject::class);
        }

        return $this->saveObject;
    }

    public function getRenderForm(): RenderFormInterface&Stub
    {
        if (!$this->renderForm instanceof RenderFormInterface) {
            $this->renderForm = $this->createStub(RenderFormInterface::class);
        }

        return $this->renderForm;
    }

    public function getRenderError(): RenderError&Stub
    {
        if (!$this->renderError instanceof RenderError) {
            $this->renderError = $this->createStub(RenderError::class);
        }

        return $this->renderError;
    }

    public function buildPlan(): ManageApiKeysEndPoint
    {
        return new ManageApiKeysEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getLoadCurrentUser(),
            $this->getFormHandling(),
            $this->getFormProcessing(),
            $this->getSaveObject(),
            $this->getRenderForm(),
            $this->getRenderError(),
            'foo.template',
        );
    }

    public function testTrainWithoutDefaultErrorTemplate(): void
    {
        $plan = new ManageApiKeysEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getLoadCurrentUser(),
            $this->getFormHandling(),
            $this->getFormProcessing(),
            $this->getSaveObject(),
            $this->getRenderForm(),
            $this->getRenderError(),
        );

        $this->assertInstanceOf(
            ManageApiKeysEndPoint::class,
            $plan->train($this->createStub(ChefInterface::class)),
        );
    }
}

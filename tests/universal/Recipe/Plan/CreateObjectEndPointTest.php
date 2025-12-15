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
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Recipe\Plan\CreateObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CreateObjectEndPoint::class)]
class CreateObjectEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?FormHandlingInterface $formHandling = null;

    private ?CreateObject $createObject = null;

    private ?FormProcessingInterface $formProcessing = null;

    private ?SlugPreparation $slugPreparation = null;

    private ?ObjectAccessControlInterface $objectAccessControl = null;

    private ?SaveObject $saveObject = null;

    private ?RedirectClientInterface $redirectClient = null;

    private ?RenderFormInterface $renderForm = null;

    private ?RenderError $renderError = null;

    /**
     * @return RecipeInterface|MockObject
     */
    public function getRecipe(): RecipeInterface
    {
        if (!$this->recipe instanceof RecipeInterface) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return FormHandlingInterface|MockObject
     */
    public function getFormHandling(): FormHandlingInterface
    {
        if (!$this->formHandling instanceof FormHandlingInterface) {
            $this->formHandling = $this->createStub(FormHandlingInterface::class);
        }

        return $this->formHandling;
    }

    /**
     * @return CreateObject|MockObject
     */
    public function getCreateObject(): CreateObject
    {
        if (!$this->createObject instanceof CreateObject) {
            $this->createObject = $this->createStub(CreateObject::class);
        }

        return $this->createObject;
    }

    /**
     * @return FormProcessingInterface|MockObject
     */
    public function getFormProcessing(): FormProcessingInterface
    {
        if (!$this->formProcessing instanceof FormProcessingInterface) {
            $this->formProcessing = $this->createStub(FormProcessingInterface::class);
        }

        return $this->formProcessing;
    }

    /**
     * @return SlugPreparation|MockObject
     */
    public function getSlugPreparation(): SlugPreparation
    {
        if (null === $this->slugPreparation) {
            $this->slugPreparation = $this->createStub(SlugPreparation::class);
        }

        return $this->slugPreparation;
    }

    /**
     * @return SaveObject|MockObject
     */
    public function getSaveObject(): SaveObject
    {
        if (null === $this->saveObject) {
            $this->saveObject = $this->createStub(SaveObject::class);
        }

        return $this->saveObject;
    }

    /**
     * @return RedirectClientInterface|MockObject
     */
    public function getRedirectClient(): RedirectClientInterface
    {
        if (null === $this->redirectClient) {
            $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        }

        return $this->redirectClient;
    }

    /**
     * @return RenderFormInterface|MockObject
     */
    public function getRenderForm(): RenderFormInterface
    {
        if (null === $this->renderForm) {
            $this->renderForm = $this->createStub(RenderFormInterface::class);
        }

        return $this->renderForm;
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

    /**
     * @return ObjectAccessControlInterface|MockObject
     */
    public function getObjectAccessControl(): ObjectAccessControlInterface
    {
        if (null === $this->objectAccessControl) {
            $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        }

        return $this->objectAccessControl;
    }

    public function buildPlan(): CreateObjectEndPoint
    {
        return new CreateObjectEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getFormHandling(),
            $this->getFormProcessing(),
            $this->getSlugPreparation(),
            $this->getSaveObject(),
            $this->getRedirectClient(),
            $this->getRenderForm(),
            $this->getRenderError(),
            $this->getObjectAccessControl()
        );
    }
}

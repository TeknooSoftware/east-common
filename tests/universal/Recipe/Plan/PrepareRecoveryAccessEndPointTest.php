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

namespace Teknoo\Tests\East\Common\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\NotifyUserAboutRecoveryAccessInterface;
use Teknoo\East\Common\Recipe\Plan\PrepareRecoveryAccessEndPoint;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Common\Recipe\Step\User\FindUserByEmail;
use Teknoo\East\Common\Recipe\Step\User\PrepareRecoveryAccess;
use Teknoo\East\Common\Recipe\Step\User\RemoveRecoveryAccess;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(PrepareRecoveryAccessEndPoint::class)]
class PrepareRecoveryAccessEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?CreateObject $createObject = null;

    private ?FormHandlingInterface $formHandling = null;

    private ?FormProcessingInterface $formProcessing = null;

    private ?FindUserByEmail $findUserByEmail = null;

    private ?JumpIfNot $jumpIfNot = null;

    private ?RemoveRecoveryAccess $removeRecoveryAccess = null;

    private ?PrepareRecoveryAccess $prepareRecoveryAccess = null;

    private ?SaveObject $saveObject = null;

    private ?NotifyUserAboutRecoveryAccessInterface $notifyUserAboutRecoveryAccess = null;

    private ?Render $render = null;

    private ?Stop $stop = null;

    private ?RenderFormInterface $renderForm = null;

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
     * @return CreateObject|MockObject
     */
    public function getCreateObject(): CreateObject
    {
        if (null === $this->createObject) {
            $this->createObject = $this->createMock(CreateObject::class);
        }

        return $this->createObject;
    }

    /**
     * @return FormHandlingInterface|MockObject
     */
    public function getFormHandling(): FormHandlingInterface
    {
        if (null === $this->formHandling) {
            $this->formHandling = $this->createMock(FormHandlingInterface::class);
        }

        return $this->formHandling;
    }

    /**
     * @return FormProcessingInterface|MockObject
     */
    public function getFormProcessing(): FormProcessingInterface
    {
        if (null === $this->formProcessing) {
            $this->formProcessing = $this->createMock(FormProcessingInterface::class);
        }

        return $this->formProcessing;
    }

    /**
     * @return FindUserByEmail|MockObject
     */
    public function getFindUserByEmail(): FindUserByEmail
    {
        if (null === $this->findUserByEmail) {
            $this->findUserByEmail = $this->createMock(FindUserByEmail::class);
        }

        return $this->findUserByEmail;
    }

    /**
     * @return JumpIfNot|MockObject
     */
    public function getJumpIfNot(): JumpIfNot
    {
        if (null === $this->jumpIfNot) {
            $this->jumpIfNot = $this->createMock(JumpIfNot::class);
        }

        return $this->jumpIfNot;
    }

    /**
     * @return RemoveRecoveryAccess|MockObject
     */
    public function getRemoveRecoveryAccess(): RemoveRecoveryAccess
    {
        if (null === $this->removeRecoveryAccess) {
            $this->removeRecoveryAccess = $this->createMock(RemoveRecoveryAccess::class);
        }

        return $this->removeRecoveryAccess;
    }

    /**
     * @return PrepareRecoveryAccess|MockObject
     */
    public function getPrepareRecoveryAccess(): PrepareRecoveryAccess
    {
        if (null === $this->prepareRecoveryAccess) {
            $this->prepareRecoveryAccess = $this->createMock(PrepareRecoveryAccess::class);
        }

        return $this->prepareRecoveryAccess;
    }

    /**
     * @return SaveObject|MockObject
     */
    public function getSaveObject(): SaveObject
    {
        if (null === $this->saveObject) {
            $this->saveObject = $this->createMock(SaveObject::class);
        }

        return $this->saveObject;
    }

    /**
     * @return NotifyUserAboutRecoveryAccessInterface|MockObject
     */
    public function getNotifyUserAboutRecoveryAccess(): NotifyUserAboutRecoveryAccessInterface
    {
        if (null === $this->notifyUserAboutRecoveryAccess) {
            $this->notifyUserAboutRecoveryAccess = $this->createMock(NotifyUserAboutRecoveryAccessInterface::class);
        }

        return $this->notifyUserAboutRecoveryAccess;
    }

    /**
     * @return Render|MockObject
     */
    public function getRender(): Render
    {
        if (null === $this->render) {
            $this->render = $this->createMock(Render::class);
        }

        return $this->render;
    }

    /**
     * @return Stop|MockObject
     */
    public function getStop(): Stop
    {
        if (null === $this->stop) {
            $this->stop = $this->createMock(Stop::class);
        }

        return $this->stop;
    }

    /**
     * @return RenderFormInterface|MockObject
     */
    public function getRenderForm(): RenderFormInterface
    {
        if (null === $this->renderForm) {
            $this->renderForm = $this->createMock(RenderFormInterface::class);
        }

        return $this->renderForm;
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

    public function buildPlan(): PrepareRecoveryAccessEndPoint
    {
        return new PrepareRecoveryAccessEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getFormHandling(),
            $this->getFormProcessing(),
            $this->getFindUserByEmail(),
            $this->getJumpIfNot(),
            $this->getRemoveRecoveryAccess(),
            $this->getPrepareRecoveryAccess(),
            $this->getSaveObject(),
            $this->getNotifyUserAboutRecoveryAccess(),
            $this->getRender(),
            $this->getStop(),
            $this->getRenderForm(),
            $this->getRenderError(),
            'foo'
        );
    }
}

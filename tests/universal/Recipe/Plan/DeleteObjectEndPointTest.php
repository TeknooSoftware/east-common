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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Plan\DeleteObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Plan\BasePlanTestTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteObjectEndPoint::class)]
class DeleteObjectEndPointTest extends TestCase
{
    use BasePlanTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?LoadObject $loadObject = null;

    private ?ObjectAccessControlInterface $objectAccessControl = null;

    private ?JumpIf $jumpIf = null;

    private ?DeleteObject $deleteObject = null;

    private ?RedirectClientInterface $redirectClient = null;

    private ?Render $render = null;

    private ?RenderError $renderError = null;

    public function getRecipe(): RecipeInterface&Stub
    {
        if (null === $this->recipe) {
            $this->recipe = $this->createStub(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getLoadObject(): LoadObject&Stub
    {
        if (null === $this->loadObject) {
            $this->loadObject = $this->createStub(LoadObject::class);
        }

        return $this->loadObject;
    }

    public function getJumpIf(): JumpIf&Stub
    {
        if (null === $this->jumpIf) {
            $this->jumpIf = $this->createStub(JumpIf::class);
        }

        return $this->jumpIf;
    }

    public function getDeleteObject(): DeleteObject&Stub
    {
        if (null === $this->deleteObject) {
            $this->deleteObject = $this->createStub(DeleteObject::class);
        }

        return $this->deleteObject;
    }

    public function getRedirectClient(): RedirectClientInterface&Stub
    {
        if (null === $this->redirectClient) {
            $this->redirectClient = $this->createStub(RedirectClientInterface::class);
        }

        return $this->redirectClient;
    }

    public function getRender(): Render&Stub
    {
        if (null === $this->render) {
            $this->render = $this->createStub(Render::class);
        }

        return $this->render;
    }

    public function getRenderError(): RenderError&Stub
    {
        if (null === $this->renderError) {
            $this->renderError = $this->createStub(RenderError::class);
        }

        return $this->renderError;
    }

    public function getObjectAccessControl(): ObjectAccessControlInterface&Stub
    {
        if (null === $this->objectAccessControl) {
            $this->objectAccessControl = $this->createStub(ObjectAccessControlInterface::class);
        }

        return $this->objectAccessControl;
    }

    public function buildPlan(): DeleteObjectEndPoint
    {
        return new DeleteObjectEndPoint(
            $this->getRecipe(),
            $this->getLoadObject(),
            $this->getDeleteObject(),
            $this->getJumpIf(),
            $this->getRedirectClient(),
            $this->getRender(),
            $this->getRenderError(),
            $this->getObjectAccessControl()
        );
    }
}

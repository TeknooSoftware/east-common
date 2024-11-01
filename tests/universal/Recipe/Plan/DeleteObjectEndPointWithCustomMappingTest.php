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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeleteObjectEndPoint::class)]
class DeleteObjectEndPointWithCustomMappingTest extends TestCase
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

    public function getRecipe(): RecipeInterface&MockObject
    {
        if (null === $this->recipe) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    public function getLoadObject(): LoadObject&MockObject
    {
        if (null === $this->loadObject) {
            $this->loadObject = $this->createMock(LoadObject::class);
        }

        return $this->loadObject;
    }

    public function getJumpIf(): JumpIf&MockObject
    {
        if (null === $this->jumpIf) {
            $this->jumpIf = $this->createMock(JumpIf::class);
        }

        return $this->jumpIf;
    }

    public function getDeleteObject(): DeleteObject&MockObject
    {
        if (null === $this->deleteObject) {
            $this->deleteObject = $this->createMock(DeleteObject::class);
        }

        return $this->deleteObject;
    }

    public function getRedirectClient(): RedirectClientInterface&MockObject
    {
        if (null === $this->redirectClient) {
            $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        }

        return $this->redirectClient;
    }

    public function getRender(): Render&MockObject
    {
        if (null === $this->render) {
            $this->render = $this->createMock(Render::class);
        }

        return $this->render;
    }

    public function getRenderError(): RenderError&MockObject
    {
        if (null === $this->renderError) {
            $this->renderError = $this->createMock(RenderError::class);
        }

        return $this->renderError;
    }

    public function getObjectAccessControl(): ObjectAccessControlInterface&MockObject
    {
        if (null === $this->objectAccessControl) {
            $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
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
            $this->getObjectAccessControl(),
            'foo.template',
            ['foo' => 'bar']
        );
    }
}

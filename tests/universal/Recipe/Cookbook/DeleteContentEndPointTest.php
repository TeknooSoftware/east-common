<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Recipe\Cookbook\DeleteContentEndPoint;
use Teknoo\East\Website\Recipe\Step\DeleteObject;
use Teknoo\East\Website\Recipe\Step\LoadObject;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Cookbook\DeleteContentEndPoint
 */
class DeleteContentEndPointTest extends TestCase
{
    use BaseCookbookTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?LoadObject $loadObject = null;

    private ?ObjectAccessControlInterface $objectAccessControl = null;

    private ?DeleteObject $deleteObject = null;

    private ?RedirectClientInterface $redirectClient = null;

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
     * @return LoadObject|MockObject
     */
    public function getLoadObject(): LoadObject
    {
        if (null === $this->loadObject) {
            $this->loadObject = $this->createMock(LoadObject::class);
        }

        return $this->loadObject;
    }

    /**
     * @return DeleteObject|MockObject
     */
    public function getDeleteObject(): DeleteObject
    {
        if (null === $this->deleteObject) {
            $this->deleteObject = $this->createMock(DeleteObject::class);
        }

        return $this->deleteObject;
    }

    /**
     * @return RedirectClientInterface|MockObject
     */
    public function getRedirectClient(): RedirectClientInterface
    {
        if (null === $this->redirectClient) {
            $this->redirectClient = $this->createMock(RedirectClientInterface::class);
        }

        return $this->redirectClient;
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

    /**
     * @return ObjectAccessControlInterface|MockObject
     */
    public function getObjectAccessControl(): ObjectAccessControlInterface
    {
        if (null === $this->objectAccessControl) {
            $this->objectAccessControl = $this->createMock(ObjectAccessControlInterface::class);
        }

        return $this->objectAccessControl;
    }

    public function buildCookbook(): DeleteContentEndPoint
    {
        return new DeleteContentEndPoint(
            $this->getRecipe(),
            $this->getLoadObject(),
            $this->getDeleteObject(),
            $this->getRedirectClient(),
            $this->getRenderError(),
            $this->getObjectAccessControl()
        );
    }
}

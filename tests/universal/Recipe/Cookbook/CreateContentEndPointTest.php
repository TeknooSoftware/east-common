<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Recipe\Cookbook\CreateContentEndPoint;
use Teknoo\East\Website\Recipe\Step\CreateObject;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\SaveObject;
use Teknoo\East\Website\Recipe\Step\SlugPreparation;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Cookbook\CreateContentEndPoint
 */
class CreateContentEndPointTest extends TestCase
{
    use BaseCookbookTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?FormHandlingInterface $formHandling = null;

    private ?CreateObject $createObject = null;

    private ?FormProcessingInterface $formProcessing = null;

    private ?SlugPreparation $slugPreparation = null;

    private ?SaveObject $saveObject = null;

    private ?RedirectClientInterface $redirectClient = null;

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
     * @return SlugPreparation|MockObject
     */
    public function getSlugPreparation(): SlugPreparation
    {
        if (null === $this->slugPreparation) {
            $this->slugPreparation = $this->createMock(SlugPreparation::class);
        }

        return $this->slugPreparation;
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

    public function buildCookbook(): CreateContentEndPoint
    {
        return new CreateContentEndPoint(
            $this->getRecipe(),
            $this->getCreateObject(),
            $this->getFormHandling(),
            $this->getFormProcessing(),
            $this->getSlugPreparation(),
            $this->getSaveObject(),
            $this->getRedirectClient(),
            $this->getRenderForm(),
            $this->getRenderError()
        );
    }
}

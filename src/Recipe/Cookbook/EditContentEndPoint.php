<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Cookbook;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Website\Contracts\Recipe\Cookbook\EditContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Recipe\Step\LoadObject;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\SaveObject;
use Teknoo\East\Website\Recipe\Step\SlugPreparation;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class EditContentEndPoint implements EditContentEndPointInterface
{
    use BaseCookbookTrait;

    private LoadObject $loadObject;

    private ?ObjectAccessControlInterface $objectAccessControl = null;

    private FormHandlingInterface $formHandling;

    private FormProcessingInterface $formProcessing;

    private SlugPreparation $slugPreparation;

    private SaveObject $saveObject;

    private RenderFormInterface $renderForm;

    private RenderError $renderError;

    public function __construct(
        RecipeInterface $recipe,
        LoadObject $loadObject,
        FormHandlingInterface $formHandling,
        FormProcessingInterface $formProcessing,
        SlugPreparation $slugPreparation,
        SaveObject $saveObject,
        RenderFormInterface $renderForm,
        RenderError $renderError,
        ?ObjectAccessControlInterface $objectAccessControl = null
    ) {
        $this->loadObject = $loadObject;
        $this->formHandling = $formHandling;
        $this->formProcessing = $formProcessing;
        $this->slugPreparation = $slugPreparation;
        $this->saveObject = $saveObject;
        $this->renderForm = $renderForm;
        $this->renderError = $renderError;
        $this->objectAccessControl = $objectAccessControl;

        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient(WriterInterface::class, 'writer'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(new Ingredient('string', 'formClass'));
        $recipe = $recipe->require(new Ingredient('array', 'formOptions'));
        $recipe = $recipe->require(new Ingredient('string', 'template'));

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, [], 00);

        if (null !== $this->objectAccessControl) {
            $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 05);
        }

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 10);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 20);

        $recipe = $recipe->cook($this->slugPreparation, SlugPreparation::class, [], 30);

        $recipe = $recipe->cook($this->saveObject, SaveObject::class, [], 40);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 50);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        return $recipe;
    }
}

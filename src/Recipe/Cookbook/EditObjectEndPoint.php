<?php

/*
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Cookbook;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\EditObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\SlugPreparation;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to load and edit a persisted object implementing the class
 * `Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class EditObjectEndPoint implements EditObjectEndPointInterface
{
    use BaseCookbookTrait;

    /**
     * @param array<string, string> $loadObjectWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly FormHandlingInterface $formHandling,
        private readonly FormProcessingInterface $formProcessing,
        private readonly ?SlugPreparation $slugPreparation,
        private readonly SaveObject $saveObject,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly ?ObjectAccessControlInterface $objectAccessControl = null,
        private readonly ?string $defaultErrorTemplate = null,
        private readonly array $loadObjectWiths = [],
    ) {
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

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, $this->loadObjectWiths, 10);

        if (null !== $this->objectAccessControl) {
            $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);
        }

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 30);

        $recipe = $recipe->cook($this->formProcessing, FormProcessingInterface::class, [], 40);

        if (null !== $this->slugPreparation) {
            $recipe = $recipe->cook($this->slugPreparation, SlugPreparation::class, [], 50);
        }

        $recipe = $recipe->cook($this->saveObject, SaveObject::class, [], 60);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class . ':refresh', [], 69);

        $recipe = $recipe->cook($this->renderForm, RenderFormInterface::class, [], 70);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', RenderFormInterface::class);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}

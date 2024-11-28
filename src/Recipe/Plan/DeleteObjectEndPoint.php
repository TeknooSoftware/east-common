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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Plan;

use Psr\Http\Message\ServerRequestInterface;
use Stringable;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\DeleteObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ObjectAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\DeleteObject;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\LoadObject;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientWithCondition;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to delete a persisted object implementing the class
 * `Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DeleteObjectEndPoint implements DeleteObjectEndPointInterface
{
    use EditablePlanTrait;

    /**
     * @param array<string, string> $loadObjectWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadObject $loadObject,
        private readonly DeleteObject $deleteObject,
        private readonly JumpIf $jumpIf,
        private readonly RedirectClientInterface $redirectClient,
        private readonly Render $render,
        private readonly RenderError $renderError,
        private readonly ?ObjectAccessControlInterface $objectAccessControl = null,
        private readonly string|Stringable|null $defaultErrorTemplate = null,
        private readonly array $loadObjectWiths = [],
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));
        $recipe = $recipe->require(
            new IngredientWithCondition(
                conditionCallback: fn (array &$workplan) => empty($workplan['api']),
                requiredType: 'string',
                name: 'route'
            )
        );

        $recipe = $recipe->cook($this->loadObject, LoadObject::class, $this->loadObjectWiths, 10);

        if (null !== $this->objectAccessControl) {
            $recipe = $recipe->cook($this->objectAccessControl, ObjectAccessControlInterface::class, [], 20);
        }

        $recipe = $recipe->cook($this->deleteObject, DeleteObject::class, [], 30);

        $recipe = $recipe->cook(
            $this->jumpIf,
            JumpIf::class,
            [
                'testValue' => 'api',
            ],
            39,
        );

        $recipe = $recipe->cook($this->redirectClient, RedirectClientInterface::class, [], 40);

        $recipe = $recipe->cook(
            $this->render,
            Render::class,
            [
                'objectInstance' => ObjectInterface::class,
            ],
            50,
        );

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', Render::class);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}

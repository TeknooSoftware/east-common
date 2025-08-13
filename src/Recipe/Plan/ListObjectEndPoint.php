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

namespace Teknoo\East\Common\Recipe\Plan;

use Psr\Http\Message\ServerRequestInterface;
use Stringable;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\ListObjectEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to list or browse persisted objects implementing the class
 * `Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ListObjectEndPoint implements ListObjectEndPointInterface
{
    use EditablePlanTrait;

    /**
     * @param array<string, string> $loadListObjectsWiths
     */
    public function __construct(
        RecipeInterface $recipe,
        private readonly ExtractPage $extractPage,
        private readonly ExtractOrder $extractOrder,
        private readonly LoadListObjects $loadListObjects,
        private readonly RenderList $renderList,
        private readonly RenderError $renderError,
        private readonly ?SearchFormLoaderInterface $searchFormLoader = null,
        private readonly ?ListObjectsAccessControlInterface $listObjectsAccessControl = null,
        private readonly string|Stringable|null $defaultErrorTemplate = null,
        private readonly array $loadListObjectsWiths = [],
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(LoaderInterface::class, 'loader'));
        $recipe = $recipe->require(new Ingredient('string', 'defaultOrderDirection'));
        $recipe = $recipe->require(new Ingredient('int', 'itemsPerPage'));
        $recipe = $recipe->require(new Ingredient('string', 'template'));

        $recipe = $recipe->cook($this->extractPage, ExtractPage::class, [], 00);

        $recipe = $recipe->cook($this->extractOrder, ExtractOrder::class, [], 10);

        if (null !== $this->searchFormLoader) {
            $recipe = $recipe->cook($this->searchFormLoader, SearchFormLoaderInterface::class, [], 20);
        }

        $recipe = $recipe->cook($this->loadListObjects, LoadListObjects::class, $this->loadListObjectsWiths, 30);

        if (null !== $this->listObjectsAccessControl) {
            $recipe = $recipe->cook(
                $this->listObjectsAccessControl,
                ListObjectsAccessControlInterface::class,
                [],
                40
            );
        }

        $recipe = $recipe->cook($this->renderList, RenderList::class, [], 50);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}

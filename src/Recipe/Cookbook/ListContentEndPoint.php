<?php

/*
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
use Teknoo\East\Website\Contracts\Recipe\Cookbook\ListContentEndPointInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Recipe\Step\ExtractOrder;
use Teknoo\East\Website\Recipe\Step\ExtractPage;
use Teknoo\East\Website\Recipe\Step\LoadListObjects;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\RenderList;
use Teknoo\East\Website\Recipe\Step\SearchFormHandling;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ListContentEndPoint implements ListContentEndPointInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private ExtractPage $extractPage,
        private ExtractOrder $extractOrder,
        private LoadListObjects $loadListObjects,
        private RenderList $renderList,
        private RenderError $renderError,
        private ?SearchFormLoaderInterface $searchFormLoader = null,
        private ?ListObjectsAccessControlInterface $listObjectsAccessControl = null
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

        $recipe = $recipe->cook($this->loadListObjects, LoadListObjects::class, [], 30);

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

        return $recipe;
    }
}

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
use Teknoo\East\Website\Contracts\Recipe\Cookbook\RenderDynamicContentEndPointInterface;
use Teknoo\East\Website\Recipe\Step\ExtractSlug;
use Teknoo\East\Website\Recipe\Step\LoadContent;
use Teknoo\East\Website\Recipe\Step\Render;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RenderDynamicContentEndPoint implements RenderDynamicContentEndPointInterface
{
    use BaseCookbookTrait;

    private ExtractSlug $extractSlug;

    private LoadContent $loadContent;

    private Render $render;

    private RenderError $renderError;

    public function __construct(
        RecipeInterface $recipe,
        ExtractSlug $extractSlug,
        LoadContent $loadContent,
        Render $render,
        RenderError $renderError
    ) {
        $this->extractSlug = $extractSlug;
        $this->loadContent = $loadContent;
        $this->render = $render;
        $this->renderError = $renderError;

        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));

        $recipe = $recipe->cook($this->extractSlug, ExtractSlug::class, [], 10);

        $recipe = $recipe->cook($this->loadContent, LoadContent::class, [], 20);

        $recipe = $recipe->cook($this->render, Render::class, [], 30);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        return $recipe;
    }
}

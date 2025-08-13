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
use Teknoo\East\Common\Contracts\Recipe\Plan\RenderMediaEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Recipe\Step\LoadMedia;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SendMedia;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to load a `Teknoo\East\Common\Object\Media` instance and send its
 * to the client to display it (images, pdf, files, etc..)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RenderMediaEndPoint implements RenderMediaEndPointInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly LoadMedia $loadMedia,
        private readonly GetStreamFromMediaInterface $getStreamFromMedia,
        private readonly SendMedia $sendMedia,
        private readonly RenderError $renderError
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient('string', 'id'));

        $recipe = $recipe->cook($this->loadMedia, LoadMedia::class, [], 10);

        $recipe = $recipe->cook($this->getStreamFromMedia, GetStreamFromMediaInterface::class, [], 20);

        $recipe = $recipe->cook($this->sendMedia, SendMedia::class, [], 30);

        return $recipe->onError(new Bowl($this->renderError, []));
    }
}

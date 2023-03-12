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

namespace Teknoo\East\CommonBundle\Recipe\Cookbook;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RedirectClientInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Recipe\Step\DisableTOTP;
use Teknoo\East\Foundation\Recipe\CookbookInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * Cookbook endpoint able to disable 2FA Authentication with TOTP/Google Auth for an user.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Disable2FA implements CookbookInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private DisableTOTP $disableTOTP,
        private readonly RedirectClientInterface $redirectClient,
        private readonly RenderError $renderError,
        private readonly ?string $defaultErrorTemplate = null,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient('string', 'route'));

        $recipe = $recipe->cook($this->disableTOTP, DisableTOTP::class, [], 10);

        $recipe = $recipe->cook($this->redirectClient, RedirectClientInterface::class, [], 20);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe->onError(new Bowl($this->renderError, []));
    }
}

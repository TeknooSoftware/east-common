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

namespace Teknoo\East\CommonBundle\Recipe\Plan;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\CommonBundle\Contracts\Recipe\Step\BuildQrCodeInterface;
use Teknoo\East\CommonBundle\Recipe\Step\GenerateQRCodeTextForTOTP;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * Plan endpoint to generate a QRCode image to register an user into you TOTP app/Google
 * Authenticator
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GenerateQRCode implements EditablePlanInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private GenerateQRCodeTextForTOTP $generateQRCodeTextForTOTP,
        private BuildQrCodeInterface $buildQrCode,
        private readonly RenderError $renderError,
        private readonly ?string $defaultErrorTemplate = null,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));

        $recipe = $recipe->cook($this->generateQRCodeTextForTOTP, GenerateQRCodeTextForTOTP::class, [], 10);

        $recipe = $recipe->cook($this->buildQrCode, BuildQrCodeInterface::class, [], 20);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe->onError(new Bowl($this->renderError, []));
    }
}

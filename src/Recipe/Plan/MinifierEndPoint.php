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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Recipe\Plan;

use Psr\Http\Message\ServerRequestInterface;
use Stringable;
use Teknoo\East\Common\Contracts\FrontAsset\MinifierInterface;
use Teknoo\East\Common\Contracts\FrontAsset\PersisterInterface;
use Teknoo\East\Common\Contracts\FrontAsset\SourceLoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Plan\MinifierEndPointInterface;
use Teknoo\East\Common\FrontAsset\FileType;
use Teknoo\East\Common\FrontAsset\FinalFile;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ComputePath;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadPersistedAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\LoadSource;
use Teknoo\East\Common\Recipe\Step\FrontAsset\MinifyAssets;
use Teknoo\East\Common\Recipe\Step\FrontAsset\PersistAsset;
use Teknoo\East\Common\Recipe\Step\FrontAsset\ReturnFile;
use Teknoo\East\Common\Recipe\Step\JumpIf;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to minify a list of assets files into an unique file, the file
 *  can be directly served by the HTTP server.
 *  The recipe can directly return the file if it's already generated (behavior defined by the parameter "noOverwrite")
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class MinifierEndPoint implements MinifierEndPointInterface
{
    use EditablePlanTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly ComputePath $computePath,
        private readonly LoadPersistedAsset $loadPersistedAsset,
        private readonly JumpIf $jumpIf,
        private readonly LoadSource $loadSource,
        private readonly MinifyAssets $minifyAssets,
        private readonly PersistAsset $persistAsset,
        private readonly ReturnFile $returnFile,
        private readonly RenderError $renderError,
        private readonly string|Stringable|null $defaultErrorTemplate = null,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(ServerRequestInterface::class, 'request'));
        $recipe = $recipe->require(new Ingredient(SourceLoaderInterface::class, 'sourceLoader'));
        $recipe = $recipe->require(new Ingredient(PersisterInterface::class, 'persister'));
        $recipe = $recipe->require(new Ingredient(MinifierInterface::class, 'minifier'));
        $recipe = $recipe->require(new Ingredient(FileType::class, 'type'));
        $recipe = $recipe->require(new Ingredient('string', 'setName'));
        $recipe = $recipe->require(
            new Ingredient(
                requiredType: 'scalar',
                name: 'noOverwrite',
                normalizeCallback: fn ($x): bool => !empty($x),
                mandatory: false,
                default: true,
            )
        );
        $recipe = $recipe->require(new Ingredient('string', 'errorTemplate'));

        $recipe = $recipe->cook($this->computePath, ComputePath::class, [], 10);

        $recipe = $recipe->cook($this->loadPersistedAsset, LoadPersistedAsset::class, [], 20);

        $recipe = $recipe->cook(
            $this->jumpIf,
            JumpIf::class,
            [
                'testValue' => FinalFile::class,
            ],
            30,
        );

        $recipe = $recipe->cook($this->loadSource, LoadSource::class, [], 40);

        $recipe = $recipe->cook($this->minifyAssets, MinifyAssets::class, [], 50);

        $recipe = $recipe->cook($this->persistAsset, PersistAsset::class, [], 60);

        $recipe = $recipe->cook($this->returnFile, ReturnFile::class, [], 70);

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        $this->addToWorkplan('nextStep', ReturnFile::class);

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', (string) $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}

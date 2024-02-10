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

namespace Teknoo\East\Common\Recipe\Cookbook;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Recipe\Cookbook\PrepareRecoveryAccessEndPointInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\RenderFormInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\User\NotifyUserAboutRecoveryAccessInterface;
use Teknoo\East\Common\Contracts\User\RecoveryAccess\AlgorithmInterface;
use Teknoo\East\Common\Contracts\User\UserInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\EmailValue;
use Teknoo\East\Common\Recipe\Step\CreateObject;
use Teknoo\East\Common\Recipe\Step\JumpIfNot;
use Teknoo\East\Common\Recipe\Step\Render;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\SaveObject;
use Teknoo\East\Common\Recipe\Step\Stop;
use Teknoo\East\Common\Recipe\Step\User\FindUserByEmail;
use Teknoo\East\Common\Recipe\Step\User\PrepareRecoveryAccess;
use Teknoo\East\Common\Recipe\Step\User\RemoveRecoveryAccess;
use Teknoo\East\CommonBundle\Recipe\Step\RenderForm;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * HTTP EndPoint Recipe able to prepare recovery access for an user identified by and email and tokens
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class PrepareRecoveryAccessEndPoint implements PrepareRecoveryAccessEndPointInterface
{
    use BaseCookbookTrait;

    public function __construct(
        RecipeInterface $recipe,
        private readonly CreateObject $createObject,
        private readonly FormHandlingInterface $formHandling,
        private readonly FormProcessingInterface $formProcessing,
        private readonly FindUserByEmail $findUserByEmail,
        private readonly JumpIfNot $jumpIfNot,
        private readonly RemoveRecoveryAccess $removeRecoveryAccess,
        private readonly PrepareRecoveryAccess $prepareRecoveryAccess,
        private readonly SaveObject $saveObject,
        private readonly NotifyUserAboutRecoveryAccessInterface $notifyUserAboutRecoveryAccess,
        private readonly Render $render,
        private readonly Stop $stop,
        private readonly RenderFormInterface $renderForm,
        private readonly RenderError $renderError,
        private readonly ?string $defaultErrorTemplate = null,
    ) {
        $this->fill($recipe);
    }

    protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $recipe->require(new Ingredient(requiredType: ServerRequestInterface::class, name: 'request'));
        $recipe = $recipe->require(new Ingredient(requiredType: LoaderInterface::class, name: 'loader'));
        $recipe = $recipe->require(new Ingredient(requiredType: WriterInterface::class, name: 'writer'));
        $recipe = $recipe->require(new Ingredient(requiredType: AlgorithmInterface::class, name: 'algorithm'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'formClass'));
        $recipe = $recipe->require(
            new Ingredient(
                requiredType: 'array',
                name: 'formOptions',
                mandatory: false,
                default: [],
            )
        );
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'formTemplate'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'submittedTemplate'));
        $recipe = $recipe->require(new Ingredient(requiredType: 'string', name: 'errorTemplate'));

        $this->addToWorkplan('objectClass', EmailValue::class);
        $recipe = $recipe->cook($this->createObject, CreateObject::class, [], 10);

        $recipe = $recipe->cook($this->formHandling, FormHandlingInterface::class, [], 20);

        $this->addToWorkplan('nextStepForForm', RenderForm::class);
        $recipe = $recipe->cook(
            action: $this->formProcessing,
            name: FormProcessingInterface::class,
            with: [
                'nextStep' => 'nextStepForForm',
            ],
            position: 30,
        );

        $recipe = $recipe->cook($this->findUserByEmail, FindUserByEmail::class, [], 30);

        $this->addToWorkplan('nextStepForJump', Render::class);
        $recipe = $recipe->cook(
            action: $this->jumpIfNot,
            name: JumpIfNot::class,
            with: [
                'nextStep' => 'nextStepForJump',
                'testValue' => UserInterface::class,
            ],
            position: 40,
        );

        $recipe = $recipe->cook($this->removeRecoveryAccess, RemoveRecoveryAccess::class, [], 50);

        $recipe = $recipe->cook($this->prepareRecoveryAccess, PrepareRecoveryAccess::class, [], 60);

        $recipe = $recipe->cook(
            $this->saveObject,
            SaveObject::class,
            [
                'object' => UserInterface::class,
            ],
            70,
        );

        $recipe = $recipe->cook(
            action: $this->notifyUserAboutRecoveryAccess,
            name: NotifyUserAboutRecoveryAccessInterface::class,
            position: 80
        );

        $recipe = $recipe->cook(
            action: $this->render,
            name: Render::class,
            with: [
                'template' => 'submittedTemplate',
            ],
            position: 90,
        );

        $recipe = $recipe->cook($this->stop, Stop::class, [], 100);

        $recipe = $recipe->cook(
            action: $this->renderForm,
            name: RenderForm::class,
            with: [
                'template' => 'formTemplate',
            ],
            position: 110,
        );

        $recipe = $recipe->onError(new Bowl($this->renderError, []));

        if (null !== $this->defaultErrorTemplate) {
            $this->addToWorkplan('errorTemplate', $this->defaultErrorTemplate);
        }

        return $recipe;
    }
}

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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;

/**
 * Recipe step to define step to use into a HTTP EndPoint Recipe to skip some recipe's steps if the form is not validate
 * by rules.
 * Symfony implementation for `FormProcessingInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FormProcessing implements FormProcessingInterface
{
    public function __invoke(
        mixed $form,
        ManagerInterface $manager,
        string $nextStep
    ): FormProcessingInterface {
        if ($form instanceof FormInterface && (!$form->isSubmitted() || !$form->isValid())) {
            $manager->continue([], $nextStep);
        }

        return $this;
    }
}

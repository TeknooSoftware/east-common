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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\FormProcessingInterface;

/**
 * Recipe step to define step to use into a HTTP EndPoint Recipe to skip some recipe's steps if the form is not validate
 * by rules.
 * Symfony implementation for `FormProcessingInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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

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

namespace Teknoo\East\CommonBundle\Twig\Extension;

use Symfony\Component\Form\FormError as SfFormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Twig\Attribute\AsTwigFunction;

use function array_pop;
use function array_reverse;
use function implode;
use function is_iterable;
use function trim;

use const PHP_EOL;

/**
 * Twig function `east_api_form_errors` to extract all errors of a form, including errors of its children (not
 * bubbled to the root form), as a list of messages indexed by the property path of the field, like `.field.subfield`
 * (brackets, used by forms without data class, like entries of collections, are removed).
 * Errors attached to the root form are indexed by `.`. Several errors on a same field are joined with a new line.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FormError
{
    /**
     * @param FormInterface<mixed> $form
     */
    private function getElementName(FormInterface $form): string
    {
        $propertyPath = $form->getPropertyPath();
        if (null === $propertyPath) {
            return $form->getName();
        }

        return trim((string) $propertyPath, '[]');
    }

    /**
     * @param string[] $viewPath
     */
    private function computePath(SfFormError $error, array $viewPath): string
    {
        $origin = $error->getOrigin();
        if (null === $origin) {
            return '.' . implode('.', $viewPath);
        }

        $elements = [];
        while (null !== $origin) {
            $elements[] = $this->getElementName($origin);
            $origin = $origin->getParent();
        }

        //Remove the root form's name
        array_pop($elements);

        return '.' . implode('.', array_reverse($elements));
    }

    /**
     * @param string[] $viewPath
     * @param array<string, string> $errors
     */
    private function collectErrors(FormView $view, array $viewPath, array &$errors): void
    {
        if (isset($view->vars['errors']) && is_iterable($view->vars['errors'])) {
            foreach ($view->vars['errors'] as $error) {
                if (!$error instanceof SfFormError) {
                    continue;
                }

                $path = $this->computePath($error, $viewPath);
                if (isset($errors[$path])) {
                    $errors[$path] .= PHP_EOL . $error->getMessage();
                } else {
                    $errors[$path] = $error->getMessage();
                }
            }
        }

        foreach ($view->children as $name => $child) {
            $this->collectErrors($child, [...$viewPath, (string) $name], $errors);
        }
    }

    /**
     * @return array<string, string>
     */
    #[AsTwigFunction('east_api_form_errors')]
    public function getFieldErrors(?FormView $view): array
    {
        $errors = [];

        if (null !== $view) {
            $this->collectErrors($view, [], $errors);
        }

        return $errors;
    }
}

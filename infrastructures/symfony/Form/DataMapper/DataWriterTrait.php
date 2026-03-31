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

namespace Teknoo\East\CommonBundle\Form\DataMapper;

use ReflectionClass;
use Symfony\Component\Form\FormInterface;
use Traversable;

use function method_exists;
use function ucfirst;

/**
 * Trait to write form values back to object properties.
 * Checks public writable (non-readonly) properties, then setters.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait DataWriterTrait
{
    abstract private static function getReflectionClass(string $mappedClass): ReflectionClass;

    /**
     * @param Traversable<mixed, FormInterface> $forms
     * @param class-string $mappedClass
     */
    private function doMapFormsToData(Traversable $forms, mixed &$viewData, string $mappedClass): void
    {
        if (!$viewData instanceof $mappedClass) {
            return;
        }

        $ref = self::getReflectionClass($mappedClass);

        foreach ($forms as $form) {
            if (!$form->isSubmitted() || $form->isDisabled()) {
                continue;
            }

            $fieldName = $form->getConfig()->getName();
            $value = $form->getData();

            if ($ref->hasProperty($fieldName)) {
                $prop = $ref->getProperty($fieldName);
                if ($prop->isPublic() && !$prop->isStatic() && !$prop->isReadOnly()) {
                    $viewData->{$fieldName} = $value;
                    continue;
                }
            }

            $setter = 'set' . ucfirst($fieldName);
            if (method_exists($viewData, $setter)) {
                $viewData->{$setter}($value);
            }
        }
    }
}

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

use ReflectionNamedType;
use Symfony\Component\Form\FormInterface;
use Teknoo\Immutable\ImmutableInterface;
use Traversable;

use function iterator_to_array;

/**
 * DataMapper for classes implementing ImmutableInterface.
 * Reads properties via public props/getters for mapDataToForms.
 * Creates a new instance via constructor reflection for mapFormsToData.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ImmutableDataMapper extends AbstractDataMapper
{
    use DataReaderTrait;


    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        $this->doMapDataToForms($viewData, $forms, $this->mappedClass);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $ref = self::getReflectionClass($this->mappedClass);
        $constructor = $ref->getConstructor();

        if (null === $constructor) {
            $viewData = $ref->newInstance();
            return;
        }

        $forms = iterator_to_array($forms);
        $params = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            $value = null;

            if ($parameter->isDefaultValueAvailable()) {
                $value = $parameter->getDefaultValue();
            }

            if (
                isset($forms[$name])
                && $forms[$name]->isSubmitted()
                && !$forms[$name]->isDisabled()
            ) {
                $value = $forms[$name]->getData();
            }

            $parameterType = $parameter->getType();
            if (
                null === $value
                && !$parameter->allowsNull()
                && $parameterType instanceof ReflectionNamedType
                && $parameterType->getName() === 'string'
            ) {
                $value = '';
            }

            $params[] = $value;
        }

        $viewData = $ref->newInstanceArgs($params);
    }

    public function support(string $class): bool
    {
        return is_a($class, ImmutableInterface::class, true);
    }
}

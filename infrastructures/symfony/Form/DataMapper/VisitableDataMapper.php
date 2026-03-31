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

use Symfony\Component\Form\FormInterface;
use Teknoo\East\Common\Contracts\Object\VisitableInterface;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * DataMapper for classes implementing VisitableInterface.
 * Uses the visitor pattern for mapDataToForms.
 * Writes via public properties or setters for mapFormsToData.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class VisitableDataMapper extends AbstractDataMapper
{
    use DataWriterTrait;

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (
            !$viewData instanceof $this->mappedClass
            || !$viewData instanceof VisitableInterface
        ) {
            return;
        }

        $visitors = [];
        foreach ($forms as $form) {
            $visitors[$form->getConfig()->getName()] = $form->setData(...);
        }

        $viewData->visit($visitors);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $this->doMapFormsToData($forms, $viewData, $this->mappedClass);
    }

    public function support(string $class): bool
    {
        return is_a($class, VisitableInterface::class, true);
    }
}

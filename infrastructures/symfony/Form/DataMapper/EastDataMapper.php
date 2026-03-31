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

use LogicException;
use Symfony\Component\Form\DataMapperInterface;
use Traversable;

/**
 * DataMapper orchestrator for Symfony forms in East context.
 * Selects the appropriate sub-mapper based on the mapped class interfaces.
 *
 * Usage: $builder->setDataMapper($eastDataMapper->configure(MyClass::class));
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class EastDataMapper implements DataMapperInterface
{
    private ?DataMapperInterface $current = null;

    /**
     * @param iterable<AbstractDataMapper> $mappers
     */
    public function __construct(
        private readonly iterable $mappers,
    ) {
    }

    /**
     * @param class-string $class
     */
    public function configure(string $class): self
    {
        $clone = clone $this;

        foreach ($this->mappers as $mapper) {
            if ($mapper->support($class)) {
                $clone->current = $mapper->configure($class);

                return $clone;
            }
        }

        throw new LogicException("EastDataMapper has no mappers supporting $class.");
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (null === $this->current) {
            throw new LogicException('EastDataMapper must be configured before use. Call configure() first.');
        }

        $this->current->mapDataToForms($viewData, $forms);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        if (null === $this->current) {
            throw new LogicException('EastDataMapper must be configured before use. Call configure() first.');
        }

        $this->current->mapFormsToData($forms, $viewData);
    }
}

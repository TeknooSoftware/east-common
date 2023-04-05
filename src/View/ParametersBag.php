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
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\View;

use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\Recipe\Ingredient\TransformableInterface;

/**
 * Bag to inject into a manager's workplan to host all parameters to pass to the final view/template.
 *
 * Because it is an mutable object, it is not necessary to forward the bag to the manager after modification.
 *
 * Two bag in a workplan can be merged via the manager's method `merge`
 * A bag can be transformed to an array before to be passed to a step by using the recipe attribute `Transform`
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ParametersBag implements MergeableInterface, TransformableInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $bag = [];

    public function set(string $name, mixed $value): self
    {
        $this->bag[$name] = $value;

        return $this;
    }

    public function merge(MergeableInterface $mergeable): MergeableInterface
    {
        if ($mergeable instanceof ParametersBag) {
            $this->bag = $mergeable->bag + $this->bag;
        }

        return $this;
    }

    public function transform(): mixed
    {
        return $this->bag;
    }
}

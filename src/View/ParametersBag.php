<?php

/*
 * East Website.
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\View;

use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\Recipe\Ingredient\TransformableInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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

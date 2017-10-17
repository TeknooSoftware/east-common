<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\Object;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Type implements DeletableInterface
{
    use ObjectTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $blocks = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): Type
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return (string) $this->template;
    }

    /**
     * @param string $template
     * @return self
     */
    public function setTemplate(string $template): Type
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return array|Block[]
     */
    public function getBlocks(): array
    {
        return \array_map(
            function ($key, $value) { return new Block($key, $value); },
            \array_keys($this->blocks),
            \array_values($this->blocks)
        );
    }

    /**
     * @param array|Block[] $blocks
     * @return self
     */
    public function setBlocks(array $blocks): Type
    {
        $this->blocks = [];

        foreach ($blocks as $block) {
            $this->blocks[$block->getName()] = $block->getType();
        }

        return $this;
    }
}

<?php

/*
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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Object;

use Teknoo\East\Website\Object\Item\Hidden;
use Teknoo\East\Website\Object\Item\Available;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsEqual;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Item implements ObjectInterface, ProxyInterface, AutomatedInterface, DeletableInterface
{
    use AutomatedTrait;
    use ObjectTrait;
    use ProxyTrait;

    protected string $name = '';

    protected ?string $slug = null;

    protected ?Content $content = null;

    protected ?int $position = null;

    protected string $location = '';

    protected bool $hidden = false;

    protected ?Item $parent = null;

    /**
     * @var iterable<Item>
     */
    protected iterable $children = [];

    protected ?string $localeField = null;

    public function __construct()
    {
        $this->initializeStateProxy();
        $this->updateStates();
    }

    /**
     * {@inheritdoc}
     * @return array<string>
     */
    public static function statesListDeclaration(): array
    {
        return [
            Hidden::class,
            Available::class,
        ];
    }

    /**
     * {@inheritdoc}
     * @return array<AssertionInterface>
     */
    protected function listAssertions(): array
    {
        return [
            (new Property([Hidden::class]))->with('hidden', new IsEqual(true)),
            (new Property([Available::class]))->with('hidden', new IsEqual(false)),
        ];
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function setName(string $name): Item
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): Item
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?Content
    {
        return $this->content;
    }

    public function setContent(?Content $content): Item
    {
        $this->content = $content;

        return $this;
    }

    public function getLocation(): string
    {
        return (string) $this->location;
    }

    public function setLocation(?string $location): Item
    {
        $this->location = (string) $location;

        return $this;
    }

    public function getPosition(): int
    {
        return (int) $this->position;
    }

    public function setPosition(int $position): Item
    {
        $this->position = $position;

        return $this;
    }


    public function isHidden(): bool
    {
        return !empty($this->hidden);
    }

    public function setHidden(bool $isHidden): Item
    {
        $this->hidden = $isHidden;

        return $this;
    }

    public function getParent(): ?Item
    {
        return $this->parent;
    }

    public function setParent(?Item $parent): Item
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return iterable<Item>
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    /**
     * @param iterable<Item> $children
     */
    public function setChildren(iterable $children): Item
    {
        $this->children = $children;

        return $this;
    }

    public function getLocaleField(): string
    {
        return (string) $this->localeField;
    }

    public function setLocaleField(string $localeField): Item
    {
        $this->localeField = $localeField;

        return $this;
    }
}

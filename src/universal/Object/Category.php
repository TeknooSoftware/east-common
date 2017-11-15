<?php

declare(strict_types=1);

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

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Teknoo\East\Website\Object\Category\Hidden;
use Teknoo\East\Website\Object\Category\Available;
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
class Category implements ProxyInterface, AutomatedInterface, Translatable, DeletableInterface
{
    use ProxyTrait,
        AutomatedTrait,
        ObjectTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $slug;
    
    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $location;

    /**
     * @var bool
     */
    private $hidden=false;

    /**
     * @var Category|null
     */
    private $parent;

    /**
     * @var Category[]
     */
    private $children;

    /**
     * @var string
     */
    private $localeField;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->initializeProxy();
        $this->updateStates();
    }

    /**
     * {@inheritdoc}
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
     */
    protected function listAssertions(): array
    {
        return [
            (new Property([Hidden::class]))->with('hidden', new IsEqual(true)),
            (new Property([Available::class]))->with('hidden', new IsEqual(false)),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return (string) $this->slug;
    }

    /**
     * @param string $slug
     * @return self
     */
    public function setSlug(string $slug = null): Category
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return (string) $this->location;
    }

    /**
     * @param string $location
     * @return self
     */
    public function setLocation(string $location): Category
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return (int) $this->position;
    }

    /**
     * @param int $position
     * @return self
     */
    public function setPosition(int $position): Category
    {
        $this->position = $position;

        return $this;
    }


    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return !empty($this->hidden);
    }

    /**
     * @param bool $isHidden
     * @return self
     */
    public function setHidden(bool $isHidden): Category
    {
        $this->hidden = $isHidden;

        return $this;
    }

    /**
     * @return null|Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param null|Category $parent
     * @return self
     */
    public function setParent(Category $parent = null): Category
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Category[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Category[] $children
     * @return self
     */
    public function setChildren($children): Category
    {
        if (!\is_array($children) && !$children instanceof \Traversable) {
            throw new \RuntimeException('Bad argument type for $children');
        }
        
        $this->children = $children;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocaleField(): string
    {
        return (string) $this->localeField;
    }

    /**
     * @param string $localeField
     *
     * @return self
     */
    public function setLocaleField(string $localeField): Category
    {
        $this->localeField = $localeField;

        return $this;
    }

    /**
     * Sets translatable locale
     *
     * @param string $locale
     *
     * @return self
     */
    public function setTranslatableLocale($locale)
    {
        $this->localeField = $locale;

        return $this;
    }

    /**
     * Sets translatable locale
     *
     * @return string
     */
    public function getTranslatableLocale(): string
    {
        return (string) $this->localeField;
    }
}

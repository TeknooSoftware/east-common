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

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;
use Teknoo\East\Website\Object\Content\Draft;
use Teknoo\East\Website\Object\Content\Published;
use Teknoo\States\LifeCycle\StatedClass\Automated\Assertion\Assertion;
use Teknoo\States\LifeCycle\StatedClass\Automated\Assertion\Property\IsInstanceOf;
use Teknoo\States\LifeCycle\StatedClass\Automated\Assertion\Property\IsNotInstanceOf;
use Teknoo\States\LifeCycle\StatedClass\Automated\AutomatedInterface;
use Teknoo\States\LifeCycle\StatedClass\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Content implements ProxyInterface, AutomatedInterface, Translatable, DeletableInterface, PublishableInterface
{
    use PublishableTrait,
        ProxyTrait,
        AutomatedTrait;

    /**
     * @var User
     */
    private $author;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $subtitle;

    /**
     * @var Category[]
     */
    private $categories;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var string[]
     */
    private $parts;

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $localeField;

    /**
     * Content constructor.
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->initializeProxy();
        $this->updateStates();
    }

    /**
     * {@inheritdoc}
     */
    public static function statesListDeclaration(): array
    {
        return [
            Draft::class,
            Published::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getStatesAssertions(): array
    {
        return [
            (new Assertion([Draft::class]))->with('publishedAt', new IsNotInstanceOf(\DateTime::class)),
            (new Assertion([Published::class]))->with('publishedAt', new IsInstanceOf(\DateTime::class)),
        ];
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     * @return self
     */
    public function setAuthor(User $author): Content
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): Content
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }

    /**
     * @return string
     */
    public function getSubtitle(): string
    {
        return (string) $this->subtitle;
    }

    /**
     * @param string $subtitle
     * @return self
     */
    public function setSubtitle(string $subtitle=null): Content
    {
        $this->subtitle = (string) $subtitle;

        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category[] $categories
     * @return self
     */
    public function setCategories($categories): Content
    {
        $this->categories = $categories;

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
    public function setSlug(string $slug=null): Content
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Type $type
     * @return self
     */
    public function setType(Type $type): Content
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return (array) \json_decode($this->parts, true);
    }

    /**
     * @param string[]|null $parts
     * @return self
     */
    public function setParts(array $parts=null): Content
    {
        $this->parts = \json_encode($parts);

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param \string[] $tags
     * @return self
     */
    public function setTags(array $tags): Content
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->description;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription(string $description=null): Content
    {
        $this->description = $description;

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
    public function setLocaleField(string $localeField): Content
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
    public function getTranslatableLocale()
    {
        return (string) $this->localeField;
    }
}

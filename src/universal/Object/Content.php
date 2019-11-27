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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Object;

use Gedmo\Translatable\Translatable;
use Teknoo\East\Website\Object\Content\Draft;
use Teknoo\East\Website\Object\Content\Published;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsInstanceOf;
use Teknoo\States\Automated\Assertion\Property\IsNotInstanceOf;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\UniversalPackage\States\Document\StandardTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Content implements
    ObjectInterface,
    ProxyInterface,
    AutomatedInterface,
    Translatable,
    DeletableInterface,
    PublishableInterface
{
    use PublishableTrait,
        StandardTrait,
        AutomatedTrait {
        AutomatedTrait::updateStates insteadof StandardTrait;
    }

    private User $author;

    private string $title;

    private string $subtitle;

    private string $slug;

    private Type $type;

    private string $parts;

    /**
     * @var string[]
     */
    private array $tags = [];

    private ?string $description = null;

    private string $localeField;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
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
    protected function listAssertions(): array
    {
        return [
            (new Property([Draft::class]))->with('publishedAt', new IsNotInstanceOf(\DateTimeInterface::class)),
            (new Property([Published::class]))->with('publishedAt', new IsInstanceOf(\DateTimeInterface::class)),
        ];
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): Content
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): string
    {
        return (string) $this->title;
    }

    public function setTitle(?string $title): Content
    {
        $this->title = (string) $title;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }

    public function getSubtitle(): string
    {
        return (string) $this->subtitle;
    }

    public function setSubtitle(?string $subtitle = null): Content
    {
        $this->subtitle = (string) $subtitle;

        return $this;
    }

    public function getSlug(): string
    {
        return (string) $this->slug;
    }

    public function setSlug(?string $slug): Content
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): Content
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return (array) \json_decode((string) $this->parts, true);
    }

    public function setParts(?array $parts): Content
    {
        $this->parts = \json_encode((array) $parts);

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): Content
    {
        $this->tags = $tags;

        return $this;
    }

    public function getDescription(): string
    {
        return (string) $this->description;
    }

    public function setDescription(?string $description): Content
    {
        $this->description = $description;

        return $this;
    }

    public function getLocaleField(): string
    {
        return (string) $this->localeField;
    }

    public function setLocaleField(string $localeField): Content
    {
        $this->localeField = $localeField;

        return $this;
    }

    public function setTranslatableLocale(string $locale): self
    {
        $this->localeField = $locale;

        return $this;
    }

    public function getTranslatableLocale(): string
    {
        return (string) $this->localeField;
    }
}

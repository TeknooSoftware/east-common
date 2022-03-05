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

namespace Teknoo\East\Website\Object;

use DateTimeInterface;
use Exception;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content\Draft;
use Teknoo\East\Website\Object\Content\Published;
use Teknoo\East\Website\Service\FindSlugService;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsInstanceOf;
use Teknoo\States\Automated\Assertion\Property\IsNotInstanceOf;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

use function json_decode;
use function json_encode;

/**
 * Stated class representing a dynamic content in the website. The content has a `Type` with several blocks.
 * Block's values are stored in object of this class in `parts`.
 * Object of this class can be translated.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Content implements
    ObjectInterface,
    TranslatableInterface,
    AutomatedInterface,
    DeletableInterface,
    PublishableInterface,
    TimestampableInterface,
    SluggableInterface
{
    use PublishableTrait;
    use AutomatedTrait;
    use ProxyTrait;

    protected ?User $author = null;

    protected string $title = '';

    protected string $subtitle = '';

    protected ?string $slug = null;

    protected ?Type $type = null;

    protected string $parts = '{}';

    /**
     * @var array<string>
     */
    protected array $tags = [];

    protected ?string $description = null;

    protected ?string $localeField = null;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->initializeStateProxy();
        $this->updateStates();
    }

    /**
     * @return array<string>
     */
    public static function statesListDeclaration(): array
    {
        return [
            Draft::class,
            Published::class,
        ];
    }

    /**
     * @return array<AssertionInterface>
     */
    protected function listAssertions(): array
    {
        return [
            (new Property([Draft::class]))->with('publishedAt', new IsNotInstanceOf(DateTimeInterface::class)),
            (new Property([Published::class]))->with('publishedAt', new IsInstanceOf(DateTimeInterface::class)),
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
        return $this->title;
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
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle = null): Content
    {
        $this->subtitle = (string) $subtitle;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface {
        $slugValue = $this->getSlug();
        if (empty($slugValue)) {
            $slugValue = $this->getTitle();
        }

        $findSlugService->process(
            $loader,
            $slugField,
            $this,
            [
                $slugValue
            ]
        );

        return $this;
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
     * @return array<mixed>
     */
    public function getParts(): array
    {
        return (array) json_decode((string) $this->parts, true);
    }

    /**
     * @param array<mixed>|null $parts
     */
    public function setParts(?array $parts): Content
    {
        $this->parts = (string) json_encode((array) $parts);

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array<string> $tags
     */
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

    public function getLocaleField(): ?string
    {
        return $this->localeField;
    }

    public function setLocaleField(?string $localeField): TranslatableInterface
    {
        $this->localeField = $localeField;

        return $this;
    }
}

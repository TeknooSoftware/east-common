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

/**
 * Abstract class to persist media in the database (image, pdf, or any binary stuff). Metadata of this media are
 * stored into an embedded MediaMetadata instance (content type, filename, alternative name).
 *
 * This class is not directly instanciable and must be inherited according to data layer :
 *   (Doctrine ODM, Doctrine ORM, ... other)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class Media implements ObjectInterface
{
    protected ?string $id = null;

    protected ?string $name = null;

    protected ?int $length = null;

    protected ?MediaMetadata $metadata = null;

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function setName(string $name): Media
    {
        $this->name = $name;

        return $this;
    }

    public function getLength(): int
    {
        return (int) $this->length;
    }

    public function setLength(int $length): Media
    {
        $this->length = $length;

        return $this;
    }

    public function getMetadata(): ?MediaMetadata
    {
        return $this->metadata;
    }

    public function setMetadata(?MediaMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}

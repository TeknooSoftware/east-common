<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Object;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Object\MediaMetadata;

/**
 * Abstract class to persist media in the database (image, pdf, or any binary stuff). Metadata of this media are
 * stored into an embedded MediaMetadata instance (content type, filename, alternative name).
 *
 * This class is not directly instanciable and must be inherited according to data layer :
 *   (Doctrine ODM, Doctrine ORM, ... other)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class Media implements IdentifiedObjectInterface
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

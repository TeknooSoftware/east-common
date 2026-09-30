<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Object;

use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;

/**
 * Abstract class to persist media in the database (image, pdf, or any binary stuff). Metadata of this media are
 * stored into an embedded MediaMetadata instance (content type, filename, alternative name).
 * Media are normalizable, only the content type, the filename and the alternative name of the metadata are exported.
 *
 * This class is not directly instanciable and must be inherited according to data layer :
 *   (Doctrine ODM, Doctrine ORM, ... other)
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[ClassGroup('default', 'api', 'crud', 'digest')]
abstract class Media implements IdentifiedObjectInterface, NormalizableInterface
{
    use AutoTrait;

    #[Normalize(['default', 'api', 'crud', 'digest'])]
    protected ?string $id = null;

    #[Normalize(['default', 'api', 'crud', 'digest'])]
    protected ?string $name = null;

    #[Normalize(['api', 'crud'])]
    protected ?int $length = null;

    #[Normalize(['api', 'crud'], loader: 'exportMetadata')]
    protected ?MediaMetadata $metadata = null;

    /**
     * Loaders are cached by the AutoTrait for all instances, they must only use the instance passed as argument
     *
     * @return array{contentType: string, fileName: string, alternative: string}|null
     */
    protected static function exportMetadata(self $media): ?array
    {
        if (null === $media->metadata) {
            return null;
        }

        return [
            'contentType' => $media->metadata->getContentType(),
            'fileName' => $media->metadata->getFileName(),
            'alternative' => $media->metadata->getAlternative(),
        ];
    }

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

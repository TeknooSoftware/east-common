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

namespace Teknoo\Tests\East\Common\Behat\Object;

use DateTimeInterface;
use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Object\PublishableInterface;
use Teknoo\East\Foundation\Normalizer\Object\AutoTrait;
use Teknoo\East\Foundation\Normalizer\Object\ClassGroup;
use Teknoo\East\Foundation\Normalizer\Object\Normalize;
use Teknoo\East\Foundation\Normalizer\Object\NormalizableInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[ClassGroup('default', 'api', 'crud', 'digest')]
class MyObject implements IdentifiedObjectInterface, DeletableInterface, PublishableInterface, NormalizableInterface
{
    use AutoTrait;

    private ?DateTimeInterface $deletedAt = null;

    #[Normalize(['crud'])]
    private ?DateTimeInterface $publishedAt = null;

    public function __construct(
        #[Normalize(['default', 'api', 'crud', 'digest'])]
        public ?string $id = null,
        #[Normalize(['default', 'api', 'crud', 'digest'])]
        public ?string $name = null,
        #[Normalize(['api', 'crud'])]
        public ?string $slug = null,
        #[Normalize(['crud'])]
        public ?string $saved = null,
    ) {
    }

    public function getId(): string
    {
        return (string) $this->id;
    }

    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTimeInterface $deletedAt): DeletableInterface
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(DateTimeInterface $dateTime): PublishableInterface
    {
        $this->publishedAt = $dateTime;

        return $this;
    }
}

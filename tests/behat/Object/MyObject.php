<?php

/*
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Common\Behat\Object;

use DateTimeInterface;
use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MyObject implements IdentifiedObjectInterface, DeletableInterface
{
    private ?DateTimeInterface $deletedAt = null;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $slug = null,
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
}

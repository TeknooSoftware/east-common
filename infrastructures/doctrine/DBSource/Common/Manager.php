<?php

/*
 * East Common.
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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;
use Teknoo\East\Common\DBSource\Manager\AlreadyStartedBatchException;
use Teknoo\East\Common\DBSource\Manager\NonStartedBatchException;

/**
 * Default implementation of `ManagerInterface` wrapping Doctrine's object manager,
 * following generic Doctrine interfaces.
 * Usable with ORM or ODM.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Manager implements ManagerInterface
{
    private bool $isInBatch = false;

    private bool $mustFlush = false;

    public function __construct(
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function openBatch(): ManagerInterface
    {
        if ($this->isInBatch) {
            throw new AlreadyStartedBatchException();
        }

        $this->isInBatch = true;

        return $this;
    }

    public function closeBatch(): ManagerInterface
    {
        if (!$this->isInBatch) {
            throw new NonStartedBatchException();
        }

        if ($this->mustFlush) {
            $this->objectManager->flush();
        }

        $this->isInBatch = false;
        $this->mustFlush = false;

        return $this;
    }

    public function persist(object $object): ManagerInterface
    {
        $this->objectManager->persist($object);

        return $this;
    }

    public function remove(object $object): ManagerInterface
    {
        $this->objectManager->remove($object);

        return $this;
    }

    public function flush(): ManagerInterface
    {
        if (!$this->isInBatch) {
            $this->objectManager->flush();
        } else {
            $this->mustFlush = true;
        }

        return $this;
    }
}

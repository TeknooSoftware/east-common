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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Service;

use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;

/**
 * Generic deleting service to delete an object (soft deletable or not) thanks to dedicated writer service to the
 * object's class. So they have one instance of this service per object's classes.
 * If the object is soft deletable, it will be flagged with a timestamp to its deletedAt property,
 * else the method "remove" of its writter will be called.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DeletingService
{
    /**
     * @param WriterInterface<ObjectInterface> $writer
     */
    public function __construct(
        private readonly WriterInterface $writer,
        private readonly DatesService $datesService,
    ) {
    }

    private function processDeletable(DeletableInterface $object): void
    {
        $this->datesService->passMeTheDate($object->setDeletedAt(...));

        if ($object instanceof ObjectInterface) {
            $this->writer->save($object);
        }
    }

    public function delete(ObjectInterface $object): DeletingService
    {
        if ($object instanceof DeletableInterface) {
            $this->processDeletable($object);

            return $this;
        }

        $this->writer->remove($object);

        return $this;
    }
}

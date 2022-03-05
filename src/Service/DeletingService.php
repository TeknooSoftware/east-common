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

namespace Teknoo\East\Website\Service;

use Teknoo\East\Website\Object\DeletableInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * Generic deleting service to delete an object (soft deletable or not) thanks to dedicated writer service to the
 * object's class. So they have one instance of this service per object's classes.
 * If the object is soft deletable, it will be flagged with a timestamp to its deletedAt property,
 * else the method "remove" of its writter will be called.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DeletingService
{
    public function __construct(
        private WriterInterface $writer,
        private DatesService $datesService,
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

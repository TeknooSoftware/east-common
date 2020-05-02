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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Service;

use Teknoo\East\Website\Object\DeletableInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DeletingService
{
    private DatesService $datesService;

    private WriterInterface $writer;

    public function __construct(WriterInterface $writer, DatesService $datesService)
    {
        $this->writer = $writer;
        $this->datesService = $datesService;
    }

    public function delete(DeletableInterface $object): DeletingService
    {
        $this->datesService->passMeTheDate([$object, 'setDeletedAt']);

        if ($object instanceof ObjectInterface) {
            $this->writer->save($object);
        }

        return $this;
    }
}

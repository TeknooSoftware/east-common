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

namespace Teknoo\East\Website\Doctrine\Translatable\Event\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Teknoo\East\Website\Doctrine\Translatable\Event\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslatableInterface;

class ODM implements AdapterInterface
{
    private LifecycleEventArgs $eventArgs;

    public function __construct(LifecycleEventArgs $eventArgs)
    {
        $this->eventArgs = $eventArgs;
    }

    public function getObjectManager(): DocumentManager
    {
        return $this->eventArgs->getObjectManager();
    }

    public function getObject(): TranslatableInterface
    {
        return $this->eventArgs->getObject();
    }

    public function getObjectClass(): string
    {
        $object = $this->getObject();

        return \get_class($object);
    }
}

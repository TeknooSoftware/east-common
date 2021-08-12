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

namespace Teknoo\East\Website\Writer;

use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Throwable;

/**
 * Object writer in charge of object `Teknoo\East\Website\Object\Item`.
 * The writer will update object's timestamp before update. The object persisted will be passed, with its new id for
 * new persisted object, to the promise, else the error is also passed to the promise.
 * Must provide an implementation of `Teknoo\East\Website\DBSource\ManagerInterface` to be able work.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ItemWriter implements WriterInterface
{
    use PersistTrait;

    /**
     * @throws Throwable
     */
    public function save(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        $this->persist($object, $promise);

        return $this;
    }
}

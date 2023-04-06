<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\East\Common\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Common\Object\User;
use Throwable;

/**
 * Object writer in charge of object `Teknoo\East\Common\Object\User`.
 * The writer will update object's timestamp before update. The object persisted will be passed, with its new id for
 * new persisted object, to the promise, else the error is also passed to the promise.
 * Must provide an implementation of `Teknoo\East\Common\Contracts\DBSource\ManagerInterface` to be able work.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<User>
 */
class UserWriter implements WriterInterface
{
    /**
     * @use PersistTrait<User>
     */
    use PersistTrait;

    /**
     * @throws Throwable
     */
    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface {
        $this->persist($object, $promise, $prefereRealDateOnUpdate);

        return $this;
    }
}

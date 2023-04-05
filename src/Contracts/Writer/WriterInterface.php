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

namespace Teknoo\East\Common\Contracts\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface defining methods to implement in writer in charge of persisted objects, to save or delete persisted objects
 * to be used into recipes of this library.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType of ObjectInterface
 */
interface WriterInterface
{
    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @return WriterInterface<TSuccessArgType>
     */
    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface;

    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @return WriterInterface<TSuccessArgType>
     */
    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface;
}

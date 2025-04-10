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

namespace Teknoo\East\Common\Contracts\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface defining methods to implement in writer in charge of persisted objects, to save or delete persisted objects
 * to be used into recipes of this library.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
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
        ?PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): WriterInterface;

    /**
     * @param TSuccessArgType $object
     * @param PromiseInterface<TSuccessArgType, mixed>|null $promise
     * @return WriterInterface<TSuccessArgType>
     */
    public function remove(ObjectInterface $object, ?PromiseInterface $promise = null): WriterInterface;
}

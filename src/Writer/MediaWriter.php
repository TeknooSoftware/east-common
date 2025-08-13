<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Writer\PersistTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Object writer in charge of object `Teknoo\East\Common\Object\Media`.
 * The writer will update object's timestamp before update. The object persisted will be passed, with its new id for
 * new persisted object, to the promise, else the error is also passed to the promise.
 * Must provide an implementation of `Teknoo\East\Common\Contracts\DBSource\ManagerInterface` to be able work.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<Media>
 */
class MediaWriter implements WriterInterface
{
    /**
     * @use PersistTrait<Media>
     */
    use PersistTrait;

    /**
     * @throws Throwable
     */
    public function save(
        ObjectInterface $object,
        ?PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): WriterInterface {
        $this->persist($object, $promise, $preferRealDateOnUpdate);

        return $this;
    }
}

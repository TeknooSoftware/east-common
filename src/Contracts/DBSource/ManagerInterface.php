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

namespace Teknoo\East\Common\Contracts\DBSource;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\DBSource\Manager\AlreadyStartedBatchException;
use Teknoo\East\Common\DBSource\Manager\NonStartedBatchException;

/**
 * To define manager about unitaries operations on persisteds objects
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ManagerInterface
{
    /**
     * To start a new batch of writing operation (flush calls will be delayed until closeBatch has called).
     * It's not like transaction behavior.
     * @throws AlreadyStartedBatchException if a transaction has been started
     */
    public function openBatch(): ManagerInterface;

    /**
     * To close and execute a batch of writing operation delayed since the call of openBatch
     *  It's not like transaction behavior.
     * @throws NonStartedBatchException if no transaction has been started
     */
    public function closeBatch(): ManagerInterface;

    /*
     * Tells the Manager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     */
    public function persist(ObjectInterface $object): ManagerInterface;

    /*
     * Tells the Manager to remove an instance managed
     */
    public function remove(ObjectInterface $object): ManagerInterface;

    /*
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     */
    public function flush(): ManagerInterface;

    /**
     * To register a doctrine filter to add criteria to all requests sended to the database server.
     * Usefull to implement some feature like soft deleted
     * If $enabling is at trye (default behavior) the filter will be automatically added.
     *
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    public function registerFilter(string $className, array $parameters = [], bool $enabling = true): ManagerInterface;

    /**
     * To enable a doctrine filter added with registerFilter
     *
     * @param class-string $className
     */
    public function enableFilter(string $className): ManagerInterface;

    /**
     * To disable a doctrine filter added with registerFilter
     *
     * @param class-string $className
     */
    public function disableFilter(string $className): ManagerInterface;
}

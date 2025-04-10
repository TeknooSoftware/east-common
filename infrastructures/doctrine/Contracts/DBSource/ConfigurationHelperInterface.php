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

namespace Teknoo\East\Common\Doctrine\Contracts\DBSource;

use Doctrine\Persistence\ObjectManager;
use Teknoo\East\Common\Contracts\DBSource\ManagerInterface;

/**
 * Contract to define helper to manage some configuration according to ORM or ODM implementation of Doctrine
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ConfigurationHelperInterface
{
    public function setManager(ManagerInterface $manager, ObjectManager $om): ConfigurationHelperInterface;

    /**
     * To register a doctrine filter to add criteria to all requests sended to the database server.
     * Usefull to implement some feature like soft deleted
     * If $enabling is at trye (default behavior) the filter will be automatically added.
     *
     * @param class-string $className
     * @param array<string, mixed> $parameters
     */
    public function registerFilter(
        string $className,
        array $parameters = [],
        bool $enabling = true,
    ): ConfigurationHelperInterface;

    /**
     * To enable a doctrine filter added with registerFilter
     *
     * @param class-string $className
     */
    public function enableFilter(string $className): ConfigurationHelperInterface;

    /**
     * To disable a doctrine filter added with registerFilter
     *
     * @param class-string $className
     */
    public function disableFilter(string $className): ConfigurationHelperInterface;
}

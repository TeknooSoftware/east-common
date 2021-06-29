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

use Teknoo\East\Foundation\Promise\PromiseInterface;

/**
 * Interface to implement according to DB abstract layer used (Doctrine ODM, Doctrine ORM, others...) to detect if an
 * object instance is ghosted by a proxy class by the abstract layer, and return the real instance to the promise,
 * or return directly the instance to the promise
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ProxyDetectorInterface
{
    public function checkIfInstanceBehindProxy(object $object, PromiseInterface $promise): ProxyDetectorInterface;
}

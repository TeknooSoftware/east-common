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

namespace Teknoo\East\Common\Contracts\User;

use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Interface to define a factory of bearer tokens, to create a key without a form and without a signed in user (for
 * example from a console command or from an API). The caller gives a new `BearerToken`, with its name and its
 * expiration date. The factory checks the name, completes the key (its value, the SHA-256 digest of its value, its
 * creation date) and adds it to the `BearerTokensAuth` of the owner. The promise gets the key: its value is available
 * only during this request. The caller must save the owner.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface BearerTokenFactoryInterface
{
    /**
     * @param PromiseInterface<BearerToken, mixed> $promise
     */
    public function create(
        User $owner,
        BearerToken $bearerToken,
        PromiseInterface $promise,
    ): BearerTokenFactoryInterface;
}

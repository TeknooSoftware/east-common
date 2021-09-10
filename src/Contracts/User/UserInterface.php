<?php

/*
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

namespace Teknoo\East\Website\Contracts\User;

use Teknoo\East\Website\Contracts\ObjectInterface;

/**
 * Base interface defining an user on a webapp/website (a visitor, an admin, an author, a subscriber, ...)
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface UserInterface extends ObjectInterface
{
    public function __toString();

    /**
     * @return iterable<string>
     */
    public function getRoles(): iterable;

    public function getUserIdentifier(): string;

    /**
     * @return iterable<AuthDataInterface>
     */
    public function getAuthData(): iterable;
}

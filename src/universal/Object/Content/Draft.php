<?php

declare(strict_types=1);

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\Object\Content;

use Teknoo\East\Website\Object\Content;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @mixin Content
 */
class Draft implements StateInterface
{
    use StateTrait;

    public function setPublishedAt(): callable
    {
        return function (\DateTimeInterface $dateTime): Content {
            $this->publishedAt = $dateTime;

            $this->updateStates();

            return $this;
        };
    }
}

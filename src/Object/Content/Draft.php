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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Object\Content;

use DateTimeInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * Content's state representing a content instance not published, aka a draft. The methode "setPublishedAt" is provided
 * only in this state. So, a published content can not be republished (but can be updated).
 *
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
        return function (DateTimeInterface $dateTime): Content {
            $this->publishedAt = $dateTime;

            $this->updateStates();

            return $this;
        };
    }
}

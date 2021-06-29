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

namespace Teknoo\East\Website\Contracts\Recipe\Step;

use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Object\Media as BaseMedia;

/**
 * Interface to define step to use into a HTTP EndPoint Recipe to fetch and put into Manager's workplan a PSR11
 * `StreamInterface` instance wrapping stream/bytes/resource handle of a persisted media
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface GetStreamFromMediaInterface
{
    public function __invoke(
        BaseMedia $media,
        ManagerInterface $manager
    ): GetStreamFromMediaInterface;
}

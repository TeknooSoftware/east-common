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

namespace Teknoo\East\Website\Object;

use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Service\FindSlugService;

/**
 * Interface to define object can be identified by a string slug to be selected easily in a request.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface SluggableInterface
{
    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface;

    public function setSlug(string $slug): SluggableInterface;
}

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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Contracts\Object;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * Interface to define object can be identified by a string slug to be selected easily in a request.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 */
interface SluggableInterface
{
    /**
     * @param LoaderInterface<SluggableInterface<TSuccessArgType>> $loader
     * @return SluggableInterface<TSuccessArgType>
     */
    public function prepareSlugNear(
        LoaderInterface $loader,
        FindSlugService $findSlugService,
        string $slugField
    ): SluggableInterface;

    /**
     * @return SluggableInterface<TSuccessArgType>
     */
    public function setSlug(string $slug): SluggableInterface;
}

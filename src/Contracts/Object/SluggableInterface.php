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

namespace Teknoo\East\Common\Contracts\Object;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Service\FindSlugService;

/**
 * Interface to define object can be identified by a string slug to be selected easily in a request.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType of ObjectInterface
 */
interface SluggableInterface
{
    /**
     * @param LoaderInterface<TSuccessArgType&SluggableInterface<TSuccessArgType>> $loader
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

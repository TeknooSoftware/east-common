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

namespace Teknoo\East\Common\Recipe\Step;

use DomainException;
use SensitiveParameter;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Step recipe to load a Media instance, from its id, thank to the Content's loader and put it into the workplan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class LoadMedia
{
    public function __construct(
        private readonly MediaLoader $mediaLoader,
    ) {
    }

    public function __invoke(string $id, ManagerInterface $manager): self
    {
        /** @var Promise<Media, mixed, mixed> $fetchPromise */
        $fetchPromise = new Promise(
            static function (Media $media) use ($manager): void {
                $manager->updateWorkPlan([Media::class => $media]);
            },
            static function (#[SensitiveParameter] Throwable $error) use ($manager): void {
                $error = new DomainException($error->getMessage(), 404, $error);

                $manager->error($error);
            }
        );

        $this->mediaLoader->load(
            $id,
            $fetchPromise,
        );

        return $this;
    }
}

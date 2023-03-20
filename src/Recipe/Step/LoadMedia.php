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

namespace Teknoo\East\Common\Recipe\Step;

use DomainException;
use Teknoo\East\Common\Loader\MediaLoader;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

/**
 * Step recipe to load a Media instance, from its id, thank to the Content's loader and put it into the workplan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
            static function (Throwable $error) use ($manager): void {
                $error = new DomainException($error->getMessage(), 404, $error);

                $manager->error($error);
            }
        );

        $this->mediaLoader->load(
            $id,
            $fetchPromise
        );

        return $this;
    }
}

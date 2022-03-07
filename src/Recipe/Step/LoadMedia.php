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

namespace Teknoo\East\Website\Recipe\Step;

use DomainException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Website\Loader\MediaLoader;
use Teknoo\East\Website\Object\Media;
use Throwable;

/**
 * Step recipe to load a Media instance, from its id, thank to the Content's loader and put it into the workplan.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class LoadMedia
{
    public function __construct(
        private MediaLoader $mediaLoader,
    ) {
    }

    public function __invoke(string $id, ManagerInterface $manager): self
    {
        $this->mediaLoader->load(
            $id,
            new Promise(
                static function (Media $media) use ($manager) {
                    $manager->updateWorkPlan([Media::class => $media]);
                },
                static function (Throwable $error) use ($manager) {
                    $error = new DomainException($error->getMessage(), 404, $error);

                    $manager->error($error);
                }
            )
        );

        return $this;
    }
}

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

namespace Teknoo\East\Website\Doctrine\Recipe\Step\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\Media as BaseMedia;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class GetStreamFromMedia implements GetStreamFromMediaInterface
{
    public function __construct(
        private GridFSRepository $repository,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function __invoke(
        BaseMedia $media,
        ManagerInterface $manager
    ): GetStreamFromMediaInterface {
        if (!$media instanceof Media) {
            $manager->error(new RuntimeException('Error this media is not compatible with this endpoint'));

            return $this;
        }

        $resource = $this->repository->openDownloadStream($media->getId());

        $stream = $this->streamFactory->createStreamFromResource($resource);

        $manager->updateWorkPlan([
            StreamInterface::class => $stream,
        ]);

        return $this;
    }
}

<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\East\Common\Doctrine\Recipe\Step\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Teknoo\East\Common\Contracts\Recipe\Step\GetStreamFromMediaInterface;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\Media as BaseMedia;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * Recipe Step to fetch Media's stream from new GridFSRepository provided by Doctrine ODM.
 * Open a resource via the GirdFS Repository, then create a new StreamInterface instance wrapping this resource and
 * put it into the workplan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class GetStreamFromMedia implements GetStreamFromMediaInterface
{
    /**
     * @param GridFSRepository<Media> $repository
     */
    public function __construct(
        private readonly GridFSRepository $repository,
        private readonly StreamFactoryInterface $streamFactory,
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

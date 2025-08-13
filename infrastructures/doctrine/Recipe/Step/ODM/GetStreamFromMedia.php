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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

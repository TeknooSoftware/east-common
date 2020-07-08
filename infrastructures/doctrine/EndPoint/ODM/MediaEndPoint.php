<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\EndPoint\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Psr\Http\Message\StreamInterface;
use Teknoo\East\FoundationBundle\EndPoint\ResponseFactoryTrait;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\Media as BaseMedia;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaEndPoint
{
    use ResponseFactoryTrait;
    use MediaEndPointTrait;

    private GridFSRepository $repository;

    public function registerRepository(DocumentManager $manager): self
    {
        $repository = $manager->getRepository(Media::class);

        if (!$repository instanceof GridFSRepository) {
            throw new \RuntimeException(
                'Error, the Media repository is not a implementation of GridFSRepository'
            );
        }

        $this->repository = $repository;

        return $this;
    }

    protected function getStream(BaseMedia $media): StreamInterface
    {
        if (!$media instanceof Media) {
            throw new \RuntimeException('Error this media is not compatible with this endpoint');
        }

        $resource = $this->repository->openDownloadStream($media->getId());

        return $this->streamFactory->createStreamFromResource($resource);
    }
}

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

namespace Teknoo\East\Website\Doctrine\Writer\ODM;

use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\ODM\MongoDB\Repository\UploadOptions;
use RuntimeException;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Doctrine\Object\Media;
use Teknoo\East\Website\Object\MediaMetadata;
use Teknoo\East\Website\Contracts\ObjectInterface;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\Website\Writer\MediaWriter as OriginalWriter;

/**
 * East Website Writer implementation, dedicated to Media used a Doctrine ODM GridFS Repository.
 * Convert metadata from MediaMetadata to UploadOptions and upload to Mongodb the file downloaded from the client.
 *  *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaWriter implements WriterInterface
{
    public function __construct(
        private GridFSRepository $repository,
        private OriginalWriter $writer,
    ) {
    }

    public function save(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof Media || !$object->getMetadata() instanceof MediaMetadata) {
            if ($promise) {
                $promise->fail(new RuntimeException('This type of media is not managed by this writer'));
            }

            return $this;
        }

        $options = new UploadOptions();
        $options->metadata = $object->getMetadata();
        $options->chunkSizeBytes = $object->getLength();

        $media = $this->repository->uploadFromFile(
            $object->getMetadata()->getLocalPath(),
            $object->getName(),
            $options
        );

        if ($promise) {
            $promise->success($media);
        }

        return $this;
    }

    public function remove(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        $this->writer->remove($object, $promise);

        return $this;
    }
}

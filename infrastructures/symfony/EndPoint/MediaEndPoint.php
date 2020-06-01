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

namespace Teknoo\East\WebsiteBundle\EndPoint;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\FoundationBundle\EndPoint\ResponseFactoryTrait;
use Teknoo\East\Website\Doctrine\Object\StoredFile;
use Teknoo\East\Website\EndPoint\MediaEndPointTrait;
use Teknoo\East\Website\Object\Media;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MediaEndPoint
{
    use ResponseFactoryTrait;
    use MediaEndPointTrait;

    private GridFSRepository $repostory;

    public function setRepostory(DocumentManager $manager): MediaEndPoint
    {
        $repostory = $manager->getRepository(StoredFile::class);
        $this->repostory = $repostory;

        return $this;
    }

    protected function getStream(Media $media)
    {
        $t = $this->repostory->findOneBy(['file_id' => $media->getId()]);
        return $this->repostory->openDownloadStream($t->getId());
    }
}

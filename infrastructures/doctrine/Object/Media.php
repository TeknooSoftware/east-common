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

namespace Teknoo\East\Website\Doctrine\Object;

use Teknoo\East\Website\Object\Media as OriginalMedia;

class Media extends OriginalMedia
{
    /**
     * @return StoredFile
     */
    public function getFile()
    {
        return parent::getFile();
    }

    /**
     * @param StoredFile $file
     */
    public function setFile($file): self
    {
        parent::setFile($file);

        if ($file instanceof \MongoGridFSFile) {
            $this->setLength((int) $file->getSize());
        }

        return $this;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        $file = $this->getFile();

        if ($file instanceof StoredFile) {
            $aa = 1;
        }

        if (\is_callable([$file, 'getResource'])) {
            $resource = $file->getResource();

            if (\is_resource($resource)) {
                return $resource;
            }
        }

        throw new \RuntimeException('Any resource are available for this media');
    }
}

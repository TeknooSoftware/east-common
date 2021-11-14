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

namespace Teknoo\East\Website\Doctrine\Repository\ODM;

use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Repository\DefaultGridFSRepository;
use MongoDB\GridFS\Bucket;
use MongoDB\GridFS\Exception\FileNotFoundException;

use function strlen;

/**
 * Repository dedicated to Media implementation as GridFS Repository to store all media (image, pdf, or other stuff)
 * into a MongoDB's GridFS via Doctrine ODM.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Media extends DefaultGridFSRepository
{
    private function getDocumentBucket(): Bucket
    {
        return $this->dm->getDocumentBucket($this->documentName);
    }

    /**
     * @param string $id
     */
    public function openDownloadStream($id)
    {
        try {
            if (24 === strlen($id)) {
                $id = $this->class->getDatabaseIdentifierValue($id);
            }

            return $this->getDocumentBucket()->openDownloadStream($id);
        } catch (FileNotFoundException) {
            throw DocumentNotFoundException::documentNotFound($this->getClassName(), $id);
        }
    }
}

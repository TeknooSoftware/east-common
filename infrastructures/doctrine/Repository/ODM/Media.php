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

namespace Teknoo\East\Common\Doctrine\Repository\ODM;

use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\Repository\DefaultGridFSRepository;
use MongoDB\GridFS\Bucket;
use MongoDB\GridFS\Exception\FileNotFoundException;
use Teknoo\East\Common\Doctrine\Object\Media as ObjectMedia;

use function strlen;

/**
 * Repository dedicated to Media implementation as GridFS Repository to store all media (image, pdf, or other stuff)
 * into a MongoDB's GridFS via Doctrine ODM.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/states Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @extends DefaultGridFSRepository<ObjectMedia>
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

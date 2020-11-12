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

class Media extends DefaultGridFSRepository
{
    private function getDocumentBucket(): Bucket
    {
        return $this->dm->getDocumentBucket($this->documentName);
    }

    public function openDownloadStream($id)
    {
        try {
            if (24 === \strlen($id)) {
                $id = $this->class->getDatabaseIdentifierValue($id);
            }

            return $this->getDocumentBucket()->openDownloadStream($id);
        } catch (FileNotFoundException $e) {
            throw DocumentNotFoundException::documentNotFound($this->getClassName(), $id);
        }
    }
}

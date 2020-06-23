<?php

/**
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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\Persistence\Adapter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\Adapter\ODM;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\Persistence\Adapter\ODM
 */
class ODMTest extends TestCase
{
    private DocumentManager $manager;


    /**
     * @return DocumentManager|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getManager(): DocumentManager
    {
        if (!$this->manager instanceof DocumentManager) {
            $this->manager = $this->createMock(DocumentManager::class);
        }

        return $this->manager;
    }

    public function build(): ODM
    {
        return new ODM($this->getManager());
    }

    public function testLoadTranslations()
    {

    }

    public function testFindTranslation()
    {

    }

    public function testRemoveAssociatedTranslations()
    {

    }

    public function testInsertTranslationRecord()
    {

    }

    public function testUpdateTranslationRecord()
    {

    }

    public function testSetTranslationValue()
    {

    }
}

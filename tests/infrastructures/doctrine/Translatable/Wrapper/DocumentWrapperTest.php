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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\Wrapper;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Types\Type;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\DocumentWrapper;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;
use Teknoo\East\Website\Object\TranslatableInterface;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\Wrapper\DocumentWrapper
 */
class DocumentWrapperTest extends TestCase
{
    private ?TranslatableInterface $object = null;

    private ?ClassMetadata $meta = null;

    /**
     * @return TranslatableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getObject(): TranslatableInterface
    {
        if (!$this->object instanceof TranslatableInterface) {
            $this->object = $this->createMock(TranslatableInterface::class);
        }

        return $this->object;
    }

    /**
     * @return ClassMetadata|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMeta(): ClassMetadata
    {
        if (!$this->meta instanceof ClassMetadata) {
            $this->meta = $this->createMock(ClassMetadata::class);
        }

        return $this->meta;
    }

    public function build(): DocumentWrapper
    {
        return new DocumentWrapper($this->getObject(), $this->getMeta());
    }

    public function testSetPropertyValue()
    {
        self::assertInstanceOf(
            WrapperInterface::class,
            $this->build()->setPropertyValue('foo', 'bar')
        );
    }

    public function testSetOriginalObjectProperty()
    {
        $manager = $this->createMock(ManagerAdapterInterface::class);
        $manager->expects(self::once())->method('setOriginalObjectProperty');

        self::assertInstanceOf(
            WrapperInterface::class,
            $this->build()->setOriginalObjectProperty('foo', 'bar')
        );
    }

    public function testUpdateTranslationRecord()
    {

    }

    public function testLinkTranslationRecord()
    {

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
}

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

namespace Teknoo\Tests\East\Website\Doctrine\Translatable\Mapping\Driver;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\Driver\FileLocator;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\Exception\InvalidMappingException;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\SimpleXmlFactoryInterface;
use Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\Xml;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\East\Website\Doctrine\Translatable\Mapping\Driver\Xml
 */
class XmlTest extends TestCase
{
    private ?FileLocator $locator = null;

    private ?SimpleXmlFactoryInterface $simpleXmlFactory = null;

    /**
     * @return FileLocator|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLocator(): FileLocator
    {
        if (!$this->locator instanceof FileLocator) {
            $this->locator = $this->createMock(FileLocator::class);
        }

        return $this->locator;
    }

    /**
     * @return SimpleXmlFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getSimpleXmlFactory(): SimpleXmlFactoryInterface
    {
        if (!$this->simpleXmlFactory instanceof SimpleXmlFactoryInterface) {
            $this->simpleXmlFactory = $this->createMock(SimpleXmlFactoryInterface::class);

            $this->simpleXmlFactory->expects(self::any())
                ->method('__invoke')
                ->willReturnCallback(fn ($file) => new \SimpleXMLElement($file, 0, true));
        }

        return $this->simpleXmlFactory;
    }

    public function build(): Xml
    {
        return new Xml($this->getLocator(), $this->getSimpleXmlFactory());
    }

    public function testReadExtendedMetadataFileNotExist()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn('Foo');

        $this->getLocator()->expects(self::any())->method('findMappingFile')->willReturn('');

        $result = [];

        self::assertInstanceOf(
            Xml::class,
            $this->build()->readExtendedMetadata($classMeta, $result)
        );

        self::assertEmpty($result);
    }

    public function testReadExtendedMetadataFileInvalid()
    {
        $this->expectException(\RuntimeException::class);

        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn('Foo');

        $this->getLocator()->expects(self::any())->method('findMappingFile')->willReturn(
            __DIR__.'/support/invalid.xml'
        );

        $result = [];

        self::assertInstanceOf(
            Xml::class,
            $this->build()->readExtendedMetadata($classMeta, $result)
        );

        self::assertEmpty($result);
    }

    public function testReadExtendedMetadataWrongTranslationClass()
    {
        $this->expectException(InvalidMappingException::class);

        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn('Foo');

        $this->getLocator()->expects(self::any())->method('findMappingFile')->willReturn(
            __DIR__.'/support/wrong-translation.xml'
        );

        $result = [];

        self::assertInstanceOf(
            Xml::class,
            $this->build()->readExtendedMetadata($classMeta, $result)
        );

        self::assertEmpty($result);
    }

    public function testReadExtendedMetadata()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn('Foo');

        $this->getLocator()->expects(self::any())->method('findMappingFile')->willReturn(
            __DIR__.'/support/valid.xml'
        );

        $result = [];

        self::assertInstanceOf(
            Xml::class,
            $this->build()->readExtendedMetadata($classMeta, $result)
        );

        self::assertNotEmpty($result);
    }

    public function testReadExtendedMetadataWithoutField()
    {
        $classMeta = $this->createMock(ClassMetadata::class);
        $classMeta->expects(self::any())->method('getName')->willReturn('Foo');

        $this->getLocator()->expects(self::any())->method('findMappingFile')->willReturn(
            __DIR__.'/support/valid-without-field.xml'
        );

        $result = [];

        self::assertInstanceOf(
            Xml::class,
            $this->build()->readExtendedMetadata($classMeta, $result)
        );

        self::assertNotEmpty($result);
        self::assertEmpty($result['fields']);
    }
}

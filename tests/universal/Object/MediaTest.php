<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Object;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\Media;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Foundation\Normalizer\EastNormalizerInterface;
use Teknoo\Tests\East\Common\Object\Traits\PopulateObjectTrait;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Media::class)]
class MediaTest extends TestCase
{
    use PopulateObjectTrait;

    public function buildObject(): Media
    {
        return new class () extends Media {
            public function getResource(): null
            {
                return null;
            }
        };
    }

    public function testGetId(): void
    {
        $this->assertEquals(
            123,
            $this->generateObjectPopulated(['id' => 123])->getId()
        );
    }

    public function testSetId(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setId('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getId()
        );
    }

    public function testSetIdExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setId(new \stdClass());
    }

    public function testGetName(): void
    {
        $this->assertEquals(
            'fooBar',
            $this->generateObjectPopulated(['name' => 'fooBar'])->getName()
        );
    }

    public function testSetName(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setName('fooBar')
        );

        $this->assertEquals(
            'fooBar',
            $object->getName()
        );
    }

    public function testSetNameExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setName(new \stdClass());
    }

    public function testGetLength(): void
    {
        $this->assertEquals(
            123,
            $this->generateObjectPopulated(['length' => 123])->getLength()
        );
    }

    public function testSetLength(): void
    {
        $object = $this->buildObject();
        $this->assertInstanceOf(
            $object::class,
            $object->setLength(123)
        );

        $this->assertEquals(
            123,
            $object->getLength()
        );
    }

    public function testSetLengthExceptionOnBadArgument(): void
    {
        $this->expectException(\Throwable::class);
        $this->buildObject()->setLength(new \stdClass());
    }

    public function testGetMetadata(): void
    {
        $object = new MediaMetadata('foo', 'bar', 'world');
        $this->assertEquals(
            $object,
            $this->generateObjectPopulated(['metadata' => $object])->getMetadata()
        );
    }

    public function testSetMetadata(): void
    {
        $mdt = new MediaMetadata('foo', 'bar', 'world');

        $object = $this->buildObject();
        $this->assertInstanceOf(
            Media::class,
            $object->setMetadata($mdt)
        );

        $this->assertEquals(
            $mdt,
            $object->getMetadata()
        );
    }

    public function testExportToMeDataBadNormalizer(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildObject()->exportToMeData(new \stdClass(), []);
    }

    public function testExportToMeDataBadContext(): void
    {
        $this->expectException(\TypeError::class);
        $this->buildObject()->exportToMeData(
            $this->createStub(EastNormalizerInterface::class),
            new \stdClass()
        );
    }

    public function testExportToMeWithDefaultGroup(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => Media::class,
                'id' => 'foo',
                'name' => 'Max',
            ]);

        $this->assertInstanceOf(
            Media::class,
            $this->buildObject()->setId('foo')->setName('Max')->exportToMeData($normalizer)
        );
    }

    public function testExportToMeWithCrudGroup(): void
    {
        $normalizer = $this->createMock(EastNormalizerInterface::class);
        $normalizer->expects($this->once())
            ->method('injectData')
            ->with([
                '@class' => Media::class,
                'id' => 'foo',
                'name' => 'bar',
                'length' => 123,
                'metadata' => [
                    'contentType' => 'image/png',
                    'fileName' => 'foo.png',
                    'alternative' => 'Foo',
                ],
            ]);

        $this->assertInstanceOf(
            Media::class,
            $this->buildObject()
                ->setId('foo')
                ->setName('bar')
                ->setLength(123)
                ->setMetadata(new MediaMetadata('image/png', 'foo.png', 'Foo', '/tmp/foo.png', 'legacy'))
                ->exportToMeData($normalizer, ['groups' => ['crud']])
        );
    }

    public function testExportToMeWithSeveralInstances(): void
    {
        $first = $this->buildObject()
            ->setId('foo')
            ->setName('foo')
            ->setMetadata(new MediaMetadata('image/png', 'foo.png', 'Foo'));

        $second = $this->buildObject()
            ->setId('bar')
            ->setName('bar')
            ->setLength(12)
            ->setMetadata(null);

        $exported = [];
        $normalizer = $this->createStub(EastNormalizerInterface::class);
        $normalizer->method('injectData')->willReturnCallback(
            function (array $data) use (&$exported, $normalizer): EastNormalizerInterface {
                $exported[] = $data;

                return $normalizer;
            }
        );

        $first->exportToMeData($normalizer, ['groups' => ['api']]);
        $second->exportToMeData($normalizer, ['groups' => ['api']]);

        $this->assertEquals(
            [
                'contentType' => 'image/png',
                'fileName' => 'foo.png',
                'alternative' => 'Foo',
            ],
            $exported[0]['metadata'],
        );
        $this->assertNull($exported[1]['metadata']);
        $this->assertEquals('bar', $exported[1]['id']);
        $this->assertEquals(12, $exported[1]['length']);
    }
}

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

namespace Teknoo\Tests\East\CommonBundle\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Attribute\AsTwigFilter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\Exception\MissingSerializerException;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

use function json_decode;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiObjectSerializing::class)]
class ApiObjectSerializingTest extends TestCase
{
    private function buildSerializer(): Serializer
    {
        $eastNormalizer = new EastNormalizer();
        $serializer = new Serializer([$eastNormalizer], [new JsonEncoder()]);
        $eastNormalizer->setNormalizer($serializer);

        return $serializer;
    }

    private function buildUser(): User
    {
        return new class () extends User {
        }
            ->setId('u1')
            ->setFirstName('Max')
            ->setLastName('Doe')
            ->setEmail('max@teknoo.software');
    }

    public function testTwigAttribute(): void
    {
        $attributes = new \ReflectionMethod(ApiObjectSerializing::class, 'serialize')->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);
        $this->assertEquals('east_api_object_serialization', $attributes[0]->newInstance()->name);
    }

    public function testSerializeIdentifiedObject(): void
    {
        $result = new ApiObjectSerializing($this->buildSerializer())->serialize(
            object: $this->buildUser(),
            context: ['groups' => ['api']],
            meta: ['foo' => 'bar'],
        );

        $this->assertEquals(
            [
                'meta' => [
                    'id' => 'u1',
                    '@class' => User::class,
                    'foo' => 'bar',
                ],
                'data' => [
                    '@class' => User::class,
                    'id' => 'u1',
                    'firstName' => 'Max',
                    'lastName' => 'Doe',
                    'email' => 'max@teknoo.software',
                ],
            ],
            json_decode($result, true),
        );
    }

    public function testSerializeArrayWithParentObject(): void
    {
        $parent = $this->createStub(IdentifiedObjectInterface::class);
        $parent->method('getId')->willReturn('p1');

        $result = new ApiObjectSerializing($this->buildSerializer())->serialize(
            object: ['foo' => 'bar'],
            parentObject: $parent,
        );

        $decoded = json_decode($result, true);
        $this->assertEquals('p1', $decoded['meta']['id']);
        $this->assertEquals($parent::class, $decoded['meta']['@class']);
        $this->assertEquals(['foo' => 'bar'], $decoded['data']);
    }

    public function testSerializeArrayWithoutParentObject(): void
    {
        $result = new ApiObjectSerializing($this->buildSerializer())->serialize(
            object: ['foo' => 'bar'],
            meta: ['deleted' => 'success'],
        );

        $this->assertEquals(
            [
                'meta' => ['deleted' => 'success'],
                'data' => ['foo' => 'bar'],
            ],
            json_decode($result, true),
        );
    }

    public function testSerializeWithoutSerializer(): void
    {
        $this->expectException(MissingSerializerException::class);

        new ApiObjectSerializing()->serialize(['foo' => 'bar']);
    }
}

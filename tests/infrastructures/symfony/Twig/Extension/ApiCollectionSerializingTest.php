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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Attribute\AsTwigFilter;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Twig\Extension\ApiCollectionSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\Exception\MissingSerializerException;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;
use Traversable;

use function json_decode;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiCollectionSerializing::class)]
class ApiCollectionSerializingTest extends TestCase
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
        $attributes = new \ReflectionMethod(ApiCollectionSerializing::class, 'serialize')->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);
        $this->assertEquals('east_api_collection_serialization', $attributes[0]->newInstance()->name);
    }

    public function testSerializeArray(): void
    {
        $result = new ApiCollectionSerializing($this->buildSerializer())->serialize(
            collection: ['a' => $this->buildUser()],
            currentPage: 2,
            countPages: 3,
            context: ['groups' => ['digest']],
            meta: ['foo' => 'bar'],
        );

        $this->assertEquals(
            [
                'meta' => [
                    'totalPages' => 3,
                    'page' => 2,
                    'count' => 1,
                    'foo' => 'bar',
                ],
                'data' => [
                    [
                        '@class' => User::class,
                        'id' => 'u1',
                        'email' => 'max@teknoo.software',
                    ],
                ],
            ],
            json_decode($result, true),
        );
    }

    public function testSerializeCountableTraversable(): void
    {
        $collection = new class ([$this->buildUser()]) implements IteratorAggregate, Countable {
            public function __construct(
                private readonly array $items,
            ) {
            }

            public function getIterator(): Traversable
            {
                return new ArrayIterator($this->items);
            }

            public function count(): int
            {
                return 42;
            }
        };

        $decoded = json_decode(
            new ApiCollectionSerializing($this->buildSerializer())->serialize(
                collection: $collection,
                currentPage: 1,
                countPages: 42,
                context: ['groups' => ['digest']],
            ),
            true,
        );

        $this->assertEquals(42, $decoded['meta']['count']);
        $this->assertCount(1, $decoded['data']);
    }

    public function testSerializeGenerator(): void
    {
        $generator = (function () {
            yield 'foo' => $this->buildUser();
            yield 'bar' => $this->buildUser();
        })();

        $decoded = json_decode(
            new ApiCollectionSerializing($this->buildSerializer())->serialize(
                collection: $generator,
                currentPage: 1,
                countPages: 1,
                context: ['groups' => ['digest']],
            ),
            true,
        );

        $this->assertEquals(2, $decoded['meta']['count']);
        $this->assertArrayHasKey(0, $decoded['data']);
        $this->assertArrayHasKey(1, $decoded['data']);
    }

    public function testSerializeEmptyCollectionIsAList(): void
    {
        $result = new ApiCollectionSerializing($this->buildSerializer())->serialize(
            collection: [],
            currentPage: 1,
            countPages: 0,
        );

        $this->assertStringContainsString('"data":[]', $result);
    }

    public function testSerializeWithoutSerializer(): void
    {
        $this->expectException(MissingSerializerException::class);

        new ApiCollectionSerializing()->serialize([], 1, 1);
    }
}

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

use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Attribute\AsTwigFilter;
use RuntimeException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Teknoo\East\CommonBundle\Twig\Extension\ApiErrorSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectSerializing;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

use function basename;
use function json_decode;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiErrorSerializing::class)]
class ApiErrorSerializingTest extends TestCase
{
    private function buildExtension(bool $exposeServerErrorMessage = false): ApiErrorSerializing
    {
        $eastNormalizer = new EastNormalizer();
        $serializer = new Serializer([$eastNormalizer], [new JsonEncoder()]);
        $eastNormalizer->setNormalizer($serializer);

        return new ApiErrorSerializing(
            new ApiObjectSerializing($serializer),
            $exposeServerErrorMessage,
        );
    }

    public function testTwigAttribute(): void
    {
        $attributes = new \ReflectionMethod(ApiErrorSerializing::class, 'serialize')->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);
        $this->assertEquals('east_api_error_serialization', $attributes[0]->newInstance()->name);
    }

    public function testSerializeClientError(): void
    {
        $this->assertEquals(
            [
                'meta' => ['error' => true, 'foo' => 'bar'],
                'data' => ['code' => 404, 'message' => 'Not found'],
            ],
            json_decode(
                $this->buildExtension()->serialize(
                    error: new DomainException('Not found', 404),
                    meta: ['foo' => 'bar'],
                ),
                true,
            ),
        );
    }

    public function testSerializeErrorWithoutCodeIsAServerError(): void
    {
        $this->assertEquals(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 500, 'message' => 'Internal Server Error'],
            ],
            json_decode(
                $this->buildExtension()->serialize(new RuntimeException('Secret database error')),
                true,
            ),
        );
    }

    public function testSerializeServerErrorWithMessageExposed(): void
    {
        $this->assertEquals(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 503, 'message' => 'Unavailable'],
            ],
            json_decode(
                $this->buildExtension(true)->serialize(new RuntimeException('Unavailable', 503)),
                true,
            ),
        );
    }

    public function testSerializeErrorWithPreviousErrors(): void
    {
        $error = new DomainException(
            'Not saved',
            400,
            new RuntimeException('Bad value', 422, new RuntimeException('Secret database error')),
        );

        $this->assertEquals(
            [
                'meta' => ['error' => true],
                'data' => [
                    'code' => 400,
                    'message' => 'Not saved',
                    'previous' => [
                        ['code' => 422, 'message' => 'Bad value'],
                        ['code' => 500, 'message' => 'Internal Server Error'],
                    ],
                ],
            ],
            json_decode($this->buildExtension()->serialize($error), true),
        );
    }

    public function testSerializeErrorWithPreviousErrorsAndMessagesExposed(): void
    {
        $error = new RuntimeException('Unavailable', 503, new RuntimeException('Database down'));

        $this->assertEquals(
            [
                'meta' => ['error' => true],
                'data' => [
                    'code' => 503,
                    'message' => 'Unavailable',
                    'previous' => [
                        ['code' => 500, 'message' => 'Database down'],
                    ],
                ],
            ],
            json_decode($this->buildExtension(true)->serialize($error), true),
        );
    }

    public function testSerializeNeverExposesClassFileLineOrTrace(): void
    {
        $serialized = $this->buildExtension(true)->serialize(
            new RuntimeException('Unavailable', 503, new DomainException('Database down')),
        );

        $this->assertStringNotContainsString('RuntimeException', $serialized);
        $this->assertStringNotContainsString('DomainException', $serialized);
        $this->assertStringNotContainsString(basename(__FILE__), $serialized);
        $this->assertStringNotContainsString('trace', $serialized);
    }
}

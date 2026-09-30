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

namespace Teknoo\Tests\East\CommonBundle\Security\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Teknoo\East\CommonBundle\Security\Listener\ApiAuthenticationFailureListener;
use Teknoo\East\CommonBundle\Twig\Extension\ApiErrorSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectSerializing;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

use function json_decode;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiAuthenticationFailureListener::class)]
class ApiAuthenticationFailureListenerTest extends TestCase
{
    private function buildListener(): ApiAuthenticationFailureListener
    {
        $eastNormalizer = new EastNormalizer();
        $serializer = new Serializer([$eastNormalizer], [new JsonEncoder()]);
        $eastNormalizer->setNormalizer($serializer);

        return new ApiAuthenticationFailureListener(
            new ApiErrorSerializing(new ApiObjectSerializing($serializer)),
        );
    }

    /**
     * @param array<string, mixed> $expected
     */
    private function assertResponse(array $expected, int $statusCode, ?Response $response): void
    {
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame('Bearer', $response->headers->get('WWW-Authenticate'));
        $this->assertEquals($expected, json_decode((string) $response->getContent(), true));
    }

    public function testWithTheResponseOfTheJwtBundle(): void
    {
        $event = new JWTNotFoundEvent(
            new BadCredentialsException(),
            new JWTAuthenticationFailureResponse('JWT Token not found'),
        );

        ($this->buildListener())($event);

        $this->assertResponse(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 401, 'message' => 'JWT Token not found'],
            ],
            401,
            $event->getResponse(),
        );
    }

    public function testWithTheStatusCodeOfTheResponseOfTheJwtBundle(): void
    {
        $event = new AuthenticationFailureEvent(
            new BadCredentialsException(),
            new JWTAuthenticationFailureResponse('Forbidden key', 403),
        );

        ($this->buildListener())($event);

        $this->assertResponse(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 403, 'message' => 'Forbidden key'],
            ],
            403,
            $event->getResponse(),
        );
    }

    public function testWithAnotherResponse(): void
    {
        $event = new AuthenticationFailureEvent(
            new BadCredentialsException(),
            new Response('foo', 401),
        );

        ($this->buildListener())($event);

        $this->assertResponse(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 401, 'message' => 'Invalid credentials.'],
            ],
            401,
            $event->getResponse(),
        );
    }

    public function testWithoutResponse(): void
    {
        $event = new AuthenticationFailureEvent(new BadCredentialsException(), null);

        ($this->buildListener())($event);

        $this->assertResponse(
            [
                'meta' => ['error' => true],
                'data' => ['code' => 401, 'message' => 'Invalid credentials.'],
            ],
            401,
            $event->getResponse(),
        );
    }
}

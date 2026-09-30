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

namespace Teknoo\East\CommonBundle\Security\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Teknoo\East\CommonBundle\Twig\Extension\ApiErrorSerializing;

use function strtr;

/**
 * Listener of authentication failures dispatched by `lexik/jwt-authentication-bundle` (JWT token not found, invalid or
 * expired, and failures of a `json_login` using its failure handler), to render them like all other errors of a JSON
 * API, with the envelope `{"meta": {"error": true}, "data": {"code": 401, "message": "..."}}`, instead of the
 * response of the bundle (`{"code": 401, "message": "..."}`).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiAuthenticationFailureListener
{
    public function __construct(
        private readonly ApiErrorSerializing $errorSerializing,
    ) {
    }

    public function __invoke(AuthenticationFailureEvent $event): void
    {
        $response = $event->getResponse();

        $statusCode = $response?->getStatusCode() ?? Response::HTTP_UNAUTHORIZED;
        if ($response instanceof JWTAuthenticationFailureResponse) {
            $message = $response->getMessage();
        } else {
            $exception = $event->getException();
            $message = strtr($exception->getMessageKey(), $exception->getMessageData());
        }

        $event->setResponse(
            JsonResponse::fromJsonString(
                $this->errorSerializing->serialize(new RuntimeException($message, $statusCode)),
                $statusCode,
                ['WWW-Authenticate' => 'Bearer'],
            )
        );
    }
}

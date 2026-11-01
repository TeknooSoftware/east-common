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

namespace Teknoo\East\Common\User;

use DateTimeInterface;
use DomainException;
use Teknoo\East\Common\Contracts\User\BearerTokenFactoryInterface;
use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\BearerTokensAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function bin2hex;
use function hash;
use function preg_match;
use function random_bytes;

/**
 * Default implementation of `BearerTokenFactoryInterface`. The value of a key is the prefix of bearer tokens followed
 * by 32 random bytes in hexadecimal. Only its SHA-256 digest is persisted: it finds the key without a slow hash.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BearerTokenFactory implements BearerTokenFactoryInterface
{
    public function __construct(
        private readonly DatesService $datesService,
        private readonly string $tokenPrefix = '',
        private readonly bool $preferRealDate = false,
    ) {
    }

    public function create(
        User $owner,
        BearerToken $bearerToken,
        PromiseInterface $promise,
    ): BearerTokenFactoryInterface {
        if (1 !== preg_match('/^[a-z][a-z0-9\_\-]{3,}$/', $bearerToken->getName())) {
            $promise->fail(new DomainException('teknoo.east.common.bearer_tokens.error.invalid_name', 400));

            return $this;
        }

        $bearerTokens = null;
        foreach ($owner->getAuthData() as $authData) {
            if ($authData instanceof BearerTokensAuth) {
                $bearerTokens = $authData;

                break;
            }
        }

        $bearerTokens ??= new BearerTokensAuth();

        if (null !== $bearerTokens->getToken($bearerToken->getName())) {
            $promise->fail(new DomainException('teknoo.east.common.bearer_tokens.error.already_exists', 400));

            return $this;
        }

        try {
            $this->datesService->passMeTheDate(
                function (DateTimeInterface $now) use ($owner, $bearerToken, $bearerTokens): void {
                    $token = $this->tokenPrefix . bin2hex(random_bytes(32));

                    $bearerToken->setToken($token);
                    $bearerToken->setTokenDigest(hash('sha256', $token));
                    $bearerToken->setCreatedAt($now);
                    $bearerToken->setExpired(false);

                    $bearerTokens->addToken($bearerToken);
                    $owner->addAuthData($bearerTokens);
                },
                $this->preferRealDate,
            );
        } catch (Throwable $error) {
            $promise->fail($error);

            return $this;
        }

        $promise->success($bearerToken);

        return $this;
    }
}

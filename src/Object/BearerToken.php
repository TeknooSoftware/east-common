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

namespace Teknoo\East\Common\Object;

use DateTimeInterface;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;

/**
 * Class to defined a persisted user's bearer token, stored into a `BearerTokensAuth` instance. Only the SHA-256 digest
 * of the token is persisted, to find the key from its token with one indexed query : the token is available only when
 * the key is created. Values of a key are immutable once they are set.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BearerToken implements AuthDataInterface
{
    private string $name = '';

    private string $token = '';

    private ?string $tokenDigest = null;

    private ?DateTimeInterface $createdAt = null;

    private ?DateTimeInterface $expiresAt = null;

    private bool $isExpired = false;

    public function __construct(
        string $name = '',
        #[SensitiveParameter]
        string $token = '',
        #[SensitiveParameter]
        ?string $tokenDigest = null,
        bool $isExpired = false,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $expiresAt = null,
    ) {
        $this->name = $name;
        $this->token = $token;
        $this->tokenDigest = $tokenDigest;
        $this->isExpired = $isExpired;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): BearerToken
    {
        if ('' === $this->name) {
            $this->name = $name;
        }

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(
        #[SensitiveParameter]
        string $token,
    ): BearerToken {
        if ('' === $this->token) {
            $this->token = $token;
        }

        return $this;
    }

    public function getTokenDigest(): ?string
    {
        return $this->tokenDigest;
    }

    public function setTokenDigest(
        #[SensitiveParameter]
        string $tokenDigest,
    ): BearerToken {
        if (null === $this->tokenDigest) {
            $this->tokenDigest = $tokenDigest;
        }

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->isExpired;
    }

    public function setExpired(bool $expired): BearerToken
    {
        if (false === $this->isExpired) {
            $this->isExpired = $expired;
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): BearerToken
    {
        if (null === $this->createdAt) {
            $this->createdAt = $createdAt;
        }

        return $this;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): BearerToken
    {
        if (null === $this->expiresAt) {
            $this->expiresAt = $expiresAt;
        }

        return $this;
    }
}

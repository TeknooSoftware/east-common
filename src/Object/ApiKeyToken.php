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
 * Class to defined a persisted user's api key, stored into an `ApiKeysAuth` instance. Only the hash of the token is
 * persisted : the token is available only when the key is created. Values of a key are immutable once they are set.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeyToken implements AuthDataInterface
{
    private string $name = '';

    private string $token = '';

    private string $tokenHash = '';

    private ?DateTimeInterface $createdAt = null;

    private ?DateTimeInterface $expiresAt = null;

    private bool $isExpired = false;

    public function __construct(
        string $name = '',
        #[SensitiveParameter]
        string $token = '',
        #[SensitiveParameter]
        string $tokenHash = '',
        bool $isExpired = false,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $expiresAt = null,
    ) {
        $this->name = $name;
        $this->token = $token;
        $this->tokenHash = $tokenHash;
        $this->isExpired = $isExpired;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ApiKeyToken
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
    ): ApiKeyToken {
        if ('' === $this->token) {
            $this->token = $token;
        }

        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(
        #[SensitiveParameter]
        string $tokenHash,
    ): ApiKeyToken {
        if ('' === $this->tokenHash) {
            $this->tokenHash = $tokenHash;
        }

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->isExpired;
    }

    public function setExpired(bool $expired): ApiKeyToken
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

    public function setCreatedAt(?DateTimeInterface $createdAt): ApiKeyToken
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

    public function setExpiresAt(?DateTimeInterface $expiresAt): ApiKeyToken
    {
        if (null === $this->expiresAt) {
            $this->expiresAt = $expiresAt;
        }

        return $this;
    }
}

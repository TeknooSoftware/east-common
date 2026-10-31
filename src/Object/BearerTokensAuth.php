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

use ArrayAccess;
use DomainException;
use RuntimeException;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\User\AuthDataInterface;

use function is_array;

/**
 * Class to defined persisted user's bearer tokens, API keys sent by an API client with each request, in the header
 * `Authorization: Bearer`, without a JWT token and without the user's password. Each key is a `BearerToken` instance,
 * identified by its name.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class BearerTokensAuth implements AuthDataInterface
{
    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private string $type = self::class;

    /**
     * @param iterable<BearerToken> $tokens
     */
    public function __construct(
        #[SensitiveParameter]
        private iterable $tokens = [],
    ) {
    }

    /**
     * @return iterable<BearerToken>
     */
    public function getTokens(): iterable
    {
        return $this->tokens;
    }

    public function getToken(string $name): ?BearerToken
    {
        foreach ($this->tokens as $existingToken) {
            if ($existingToken->getName() === $name) {
                return $existingToken;
            }
        }

        return null;
    }

    public function addToken(BearerToken $token): self
    {
        $tokenName = $token->getName();
        foreach ($this->tokens as $existingToken) {
            if ($existingToken->getName() === $tokenName) {
                throw new DomainException('teknoo.east.common.bearer_tokens.error.already_exists', 400);
            }
        }

        if (!is_array($this->tokens) && !$this->tokens instanceof ArrayAccess) {
            throw new RuntimeException('teknoo.east.common.bearer_tokens.error.list_not_accessible', 500);
        }

        $this->tokens[] = $token;

        return $this;
    }

    public function removeToken(string $name): self
    {
        $filteredTokens = [];
        foreach ($this->tokens as $token) {
            if ($token->getName() !== $name) {
                $filteredTokens[] = $token;
            }
        }

        $this->tokens = $filteredTokens;

        return $this;
    }
}

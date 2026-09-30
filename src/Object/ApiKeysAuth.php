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
 * Class to defined persisted user's api keys, to authenticate it on an API (like a JSON login to get a JWT token)
 * without its password. Each key is an `ApiKeyToken` instance, identified by its name.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ApiKeysAuth implements AuthDataInterface
{
    //Constructor promoted properties are not defined when object is created without calling constructor
    //(like with doctrine)

    private string $type = self::class;

    /**
     * @param iterable<ApiKeyToken> $tokens
     */
    public function __construct(
        #[SensitiveParameter]
        private iterable $tokens = [],
    ) {
    }

    /**
     * @return iterable<ApiKeyToken>
     */
    public function getTokens(): iterable
    {
        return $this->tokens;
    }

    public function getToken(string $name): ?ApiKeyToken
    {
        foreach ($this->tokens as $existingToken) {
            if ($existingToken->getName() === $name) {
                return $existingToken;
            }
        }

        return null;
    }

    public function addToken(ApiKeyToken $token): self
    {
        $tokenName = $token->getName();
        foreach ($this->tokens as $existingToken) {
            if ($existingToken->getName() === $tokenName) {
                throw new DomainException('teknoo.east.common.api_keys.error.already_exists', 400);
            }
        }

        if (!is_array($this->tokens) && !$this->tokens instanceof ArrayAccess) {
            throw new RuntimeException('teknoo.east.common.api_keys.error.list_not_accessible', 500);
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

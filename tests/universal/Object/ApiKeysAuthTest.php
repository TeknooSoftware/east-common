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

namespace Teknoo\Tests\East\Common\Object;

use DomainException;
use MultipleIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeysAuth::class)]
class ApiKeysAuthTest extends TestCase
{
    private function createToken(string $name): ApiKeyToken&Stub
    {
        $token = $this->createStub(ApiKeyToken::class);
        $token->method('getName')->willReturn($name);

        return $token;
    }

    public function testGetTokens(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('a'), $this->createToken('b')]);

        $this->assertCount(2, [...$apiKeys->getTokens()]);
    }

    public function testGetTokenFound(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $found = $apiKeys->getToken('y');

        $this->assertInstanceOf(ApiKeyToken::class, $found);
        $this->assertSame('y', $found->getName());
    }

    public function testGetTokenNotFound(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $this->assertNotInstanceOf(ApiKeyToken::class, $apiKeys->getToken('z'));
    }

    public function testAddTokenDuplicateThrows(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('dup')]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('teknoo.east.common.api_keys.error.already_exists');
        $this->expectExceptionCode(400);

        $apiKeys->addToken($this->createToken('dup'));
    }

    public function testAddTokenNotAvailable(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: new MultipleIterator());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('teknoo.east.common.api_keys.error.list_not_accessible');
        $this->expectExceptionCode(500);

        $apiKeys->addToken($this->createToken('dup'));
    }

    public function testAddTokenNoDuplicateReturnsSelf(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('a')]);

        $result = $apiKeys->addToken($added = $this->createToken('b'));

        $this->assertSame($apiKeys, $result);
        $this->assertSame($added, $apiKeys->getToken('b'));
    }

    public function testRemoveToken(): void
    {
        $apiKeys = new ApiKeysAuth(tokens: [$this->createToken('a'), $this->createToken('b')]);

        $this->assertSame($apiKeys, $apiKeys->removeToken('a'));
        $remaining = [...$apiKeys->getTokens()];

        $this->assertCount(1, $remaining);
        $this->assertSame('b', $remaining[0]->getName());
    }
}

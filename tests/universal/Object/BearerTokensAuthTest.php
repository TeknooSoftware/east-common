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
use Teknoo\East\Common\Object\BearerTokensAuth;
use Teknoo\East\Common\Object\BearerToken;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(BearerTokensAuth::class)]
class BearerTokensAuthTest extends TestCase
{
    private function createToken(string $name): BearerToken&Stub
    {
        $token = $this->createStub(BearerToken::class);
        $token->method('getName')->willReturn($name);

        return $token;
    }

    public function testGetTokens(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('a'), $this->createToken('b')]);

        $this->assertCount(2, [...$bearerTokens->getTokens()]);
    }

    public function testGetTokenFound(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $found = $bearerTokens->getToken('y');

        $this->assertInstanceOf(BearerToken::class, $found);
        $this->assertSame('y', $found->getName());
    }

    public function testGetTokenNotFound(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('x'), $this->createToken('y')]);

        $this->assertNotInstanceOf(BearerToken::class, $bearerTokens->getToken('z'));
    }

    public function testAddTokenDuplicateThrows(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('dup')]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('teknoo.east.common.bearer_tokens.error.already_exists');
        $this->expectExceptionCode(400);

        $bearerTokens->addToken($this->createToken('dup'));
    }

    public function testAddTokenNotAvailable(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: new MultipleIterator());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('teknoo.east.common.bearer_tokens.error.list_not_accessible');
        $this->expectExceptionCode(500);

        $bearerTokens->addToken($this->createToken('dup'));
    }

    public function testAddTokenNoDuplicateReturnsSelf(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('a')]);

        $result = $bearerTokens->addToken($added = $this->createToken('b'));

        $this->assertSame($bearerTokens, $result);
        $this->assertSame($added, $bearerTokens->getToken('b'));
    }

    public function testRemoveToken(): void
    {
        $bearerTokens = new BearerTokensAuth(tokens: [$this->createToken('a'), $this->createToken('b')]);

        $this->assertSame($bearerTokens, $bearerTokens->removeToken('a'));
        $remaining = [...$bearerTokens->getTokens()];

        $this->assertCount(1, $remaining);
        $this->assertSame('b', $remaining[0]->getName());
    }
}

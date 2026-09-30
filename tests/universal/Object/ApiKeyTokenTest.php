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

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\ApiKeyToken;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeyToken::class)]
class ApiKeyTokenTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $created = new DateTimeImmutable('-1 day');
        $expires = new DateTimeImmutable('+1 day');

        $token = new ApiKeyToken(
            name: 'my-token',
            token: 'secret',
            tokenHash: 'hashed',
            isExpired: true,
            createdAt: $created,
            expiresAt: $expires,
        );

        $this->assertSame('my-token', $token->getName());
        $this->assertSame('secret', $token->getToken());
        $this->assertSame('hashed', $token->getTokenHash());
        $this->assertTrue($token->isExpired());
        $this->assertSame($created, $token->getCreatedAt());
        $this->assertSame($expires, $token->getExpiresAt());
    }

    public function testSetNameOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $this->assertSame($token, $token->setName('first'));
        $token->setName('second'); // must be ignored

        $this->assertSame('first', $token->getName());
    }

    public function testSetTokenOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $this->assertSame($token, $token->setToken('abc'));
        $token->setToken('def'); // must be ignored

        $this->assertSame('abc', $token->getToken());
        $this->assertSame('', $token->getTokenHash());
    }

    public function testSetTokenHashOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $this->assertSame($token, $token->setTokenHash('hash1'));
        $token->setTokenHash('hash2'); // must be ignored
        $token->setToken('raw-token');

        $this->assertSame('hash1', $token->getTokenHash());
        $this->assertSame('raw-token', $token->getToken());
    }

    public function testSetExpiredOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $this->assertSame($token, $token->setExpired(true));
        $token->setExpired(false); // must be ignored, an expired key stays expired

        $this->assertTrue($token->isExpired());
    }

    public function testSetCreatedAtOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $created1 = new DateTimeImmutable('-2 hours');
        $created2 = new DateTimeImmutable('-1 hour');

        $this->assertSame($token, $token->setCreatedAt($created1));
        $token->setCreatedAt($created2); // must be ignored

        $this->assertSame($created1, $token->getCreatedAt());
    }

    public function testSetCreatedAtWhenExpiresAtIsAlreadySet(): void
    {
        //The form sets the expiration date before the creation date
        $token = new ApiKeyToken();
        $token->setExpiresAt(new DateTimeImmutable('+1 day'));

        $created = new DateTimeImmutable('-1 hour');
        $token->setCreatedAt($created);

        $this->assertSame($created, $token->getCreatedAt());
    }

    public function testSetExpiresAtOnlyOnce(): void
    {
        $token = new ApiKeyToken();
        $expires1 = new DateTimeImmutable('+1 day');
        $expires2 = new DateTimeImmutable('+2 days');

        $this->assertSame($token, $token->setExpiresAt($expires1));
        $token->setExpiresAt($expires2); // must be ignored

        $this->assertSame($expires1, $token->getExpiresAt());
    }
}

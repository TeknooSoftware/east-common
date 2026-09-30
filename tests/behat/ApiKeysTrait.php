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

namespace Teknoo\Tests\East\Common\Behat;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;

use function base64_decode;
use function explode;
use function json_decode;
use function strtr;

/**
 * Steps to test API keys of users and JWT tokens : keys persisted with the user, and content of JWT tokens.
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ApiKeysTrait
{
    private function getApiKeysOfTheUser(): ?ApiKeysAuth
    {
        Assert::assertInstanceOf(User::class, $this->user);

        foreach ($this->user->getAuthData() as $authData) {
            if ($authData instanceof ApiKeysAuth) {
                return $authData;
            }
        }

        return null;
    }

    #[Given('an API key :name for the user with:')]
    public function anApiKeyForTheUserWith(string $name, TableNode $values): void
    {
        $values = $values->getRowsHash();

        $apiKey = new ApiKeyToken(
            name: $name,
            tokenHash: new SodiumPasswordHasher()->hash($values['secret']),
            isExpired: 'true' === $values['expired'],
            createdAt: new DateTimeImmutable($values['createdAt']),
            expiresAt: new DateTimeImmutable($values['expiresAt']),
        );

        $apiKeys = $this->getApiKeysOfTheUser() ?? new ApiKeysAuth();
        $apiKeys->addToken($apiKey);
        $this->user->addAuthData($apiKeys);
    }

    #[Then('only the hash of the secret :secret is stored with the API key :name of the user')]
    public function onlyTheHashOfTheSecretIsStoredWithTheApiKeyOfTheUser(string $secret, string $name): void
    {
        $apiKey = $this->getApiKeysOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $apiKey, "The user has no API key `{$name}`");

        $secret = $this->replaceRememberedValues($secret);
        Assert::assertNotEmpty($apiKey->getTokenHash());
        Assert::assertNotEquals($secret, $apiKey->getTokenHash());
        Assert::assertTrue(new SodiumPasswordHasher()->verify($apiKey->getTokenHash(), $secret));
    }

    #[Then('the JWT token :token is issued to :username')]
    #[Then('the JWT token :token is issued to :username and expires at :date')]
    public function theJwtTokenIsIssuedTo(string $token, string $username, ?string $date = null): void
    {
        $parts = explode('.', $this->replaceRememberedValues($token));
        Assert::assertCount(3, $parts, 'A JWT token must have three parts');

        $payload = json_decode((string) base64_decode(strtr($parts[1], '-_', '+/')), true);
        Assert::assertIsArray($payload);
        Assert::assertEquals($username, $payload['username'] ?? null);

        if (null !== $date) {
            Assert::assertEquals(
                $date,
                new DateTimeImmutable('@' . ($payload['exp'] ?? 0))->format('Y-m-d H:i:s'),
            );
        }
    }
}

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
use Behat\Step\When;
use DateTimeImmutable;
use PHPUnit\Framework\Assert;
use SensitiveParameter;
use Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher;
use Teknoo\East\Common\Contracts\User\ApiKeyFactoryInterface;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function base64_decode;
use function explode;
use function hash;
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
    /**
     * Key given by the key factory to its caller, with its value
     */
    private ?ApiKeyToken $createdApiKey = null;

    /**
     * Error given by the key factory to its caller
     */
    private ?Throwable $apiKeyFactoryError = null;

    private function createApiKeyWithTheFactory(string $factoryId, ApiKeyToken $apiKey): void
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $factory = $this->symfonyKernel->getContainer()->get($factoryId);
        Assert::assertInstanceOf(ApiKeyFactoryInterface::class, $factory);

        $this->createdApiKey = null;
        $this->apiKeyFactoryError = null;

        /** @var Promise<ApiKeyToken, mixed, mixed> $promise */
        $promise = new Promise(
            function (ApiKeyToken $apiKey): void {
                $this->createdApiKey = $apiKey;
            },
            function (#[SensitiveParameter] Throwable $error): void {
                $this->apiKeyFactoryError = $error;
            },
        );

        $factory->create($this->user, $apiKey, $promise);
    }

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

    #[When('the key factory creates the API key :name, expiring at :date')]
    #[When('the key factory creates the API key :name with the scope :scope, expiring at :date')]
    public function theKeyFactoryCreatesTheApiKeyForTheUser(string $name, string $date, ?string $scope = null): void
    {
        $this->createApiKeyWithTheFactory(
            ApiKeyFactoryInterface::class,
            new ApiKeyToken(name: $name, expiresAt: new DateTimeImmutable($date), scope: $scope),
        );
    }

    #[When('the key factory with scopes creates the API key :name, expiring at :date')]
    #[When('the key factory with scopes creates the API key :name with the scope :scope, expiring at :date')]
    public function theKeyFactoryWithScopesCreatesTheApiKeyForTheUser(
        string $name,
        string $date,
        ?string $scope = null,
    ): void {
        $this->createApiKeyWithTheFactory(
            'teknoo.east.common.behat.api_key_factory.with_scopes',
            new ApiKeyToken(name: $name, expiresAt: new DateTimeImmutable($date), scope: $scope),
        );
    }

    #[Then('the key factory gives the value of the API key :name, remembered as :remembered')]
    public function theKeyFactoryGivesTheValueOfTheApiKey(string $name, string $remembered): void
    {
        Assert::assertNull($this->apiKeyFactoryError);
        Assert::assertInstanceOf(ApiKeyToken::class, $this->createdApiKey);
        Assert::assertSame($name, $this->createdApiKey->getName());
        Assert::assertMatchesRegularExpression('/^ak_[0-9a-f]{64}$/', $this->createdApiKey->getToken());
        Assert::assertSame($this->createdApiKey, $this->getApiKeysOfTheUser()?->getToken($name));

        $this->remembered[$remembered] = $this->createdApiKey->getToken();
    }

    #[Then('the key factory refuses the API key with the error :message')]
    public function theKeyFactoryRefusesTheApiKeyWithTheError(string $message): void
    {
        Assert::assertNull($this->createdApiKey);
        Assert::assertInstanceOf(Throwable::class, $this->apiKeyFactoryError);
        Assert::assertSame($message, $this->apiKeyFactoryError->getMessage());
        Assert::assertSame(400, $this->apiKeyFactoryError->getCode());
    }

    #[Then('the user has no API key :name')]
    public function theUserHasNoApiKey(string $name): void
    {
        Assert::assertNull($this->getApiKeysOfTheUser()?->getToken($name));
    }

    #[Then('the API key :name of the user was created at :createdAt and expires at :expiresAt')]
    public function theApiKeyOfTheUserWasCreatedAtAndExpiresAt(string $name, string $createdAt, string $expiresAt): void
    {
        $apiKey = $this->getApiKeysOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $apiKey, "The user has no API key `{$name}`");

        Assert::assertSame($createdAt, $apiKey->getCreatedAt()?->format('Y-m-d H:i:s'));
        Assert::assertSame($expiresAt, $apiKey->getExpiresAt()?->format('Y-m-d H:i:s'));
        Assert::assertFalse($apiKey->isExpired());
    }

    #[Then('the API key :name of the user has the scope :scope')]
    #[Then('the API key :name of the user has no scope')]
    public function theApiKeyOfTheUserHasTheScope(string $name, ?string $scope = null): void
    {
        $apiKey = $this->getApiKeysOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $apiKey, "The user has no API key `{$name}`");

        Assert::assertSame($scope, $apiKey->getScope());
    }

    #[Then('the digest of the secret :secret is stored with the API key :name of the user')]
    public function theDigestOfTheSecretIsStoredWithTheApiKeyOfTheUser(string $secret, string $name): void
    {
        $apiKey = $this->getApiKeysOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $apiKey, "The user has no API key `{$name}`");

        Assert::assertSame(hash('sha256', $this->replaceRememberedValues($secret)), $apiKey->getTokenDigest());
    }

    #[Given('a user without password')]
    public function aUserWithoutPassword(): void
    {
        $this->symfonyKernel->boot();

        $object = new User();
        $object->setId('serviceid');
        $object->setEmail('service@teknoo.software')
            ->setFirstName('ser')
            ->setLastName('vice')
            ->setRoles(['ROLE_USER'])
            ->setAuthData([]);

        $this->user = $object;
        $this->getObjectRepository()->setObject(['email' => 'service@teknoo.software'], $object);
    }

    #[Given('the user is found from the digest of the secret :secret')]
    public function theUserIsFoundFromTheDigestOfTheSecret(string $secret): void
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $this->getObjectRepository()->setObject(
            ['authData.tokens.token_digest' => hash('sha256', $this->replaceRememberedValues($secret))],
            $this->user,
        );
    }

    #[Given('the API key :name of the user is marked as expired')]
    public function theApiKeyOfTheUserIsMarkedAsExpired(string $name): void
    {
        $apiKey = $this->getApiKeysOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(ApiKeyToken::class, $apiKey, "The user has no API key `{$name}`");

        $apiKey->setExpired(true);
    }

    #[Given('the API key :name of the user is removed')]
    public function theApiKeyOfTheUserIsRemoved(string $name): void
    {
        $this->getApiKeysOfTheUser()?->removeToken($name);
        $this->theUserHasNoApiKey($name);
    }

    #[Given('the user is disabled')]
    public function theUserIsDisabled(): void
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $this->user->setActive(false);
    }
}

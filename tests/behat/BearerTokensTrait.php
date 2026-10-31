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
use RuntimeException;
use SensitiveParameter;
use Teknoo\East\Common\Contracts\User\BearerTokenFactoryInterface;
use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\BearerTokensAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

use function hash;

/**
 * Steps to test the bearer tokens of users : keys created by the bearer token factory and persisted with the user.
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BearerTokensTrait
{
    /**
     * Key given by the bearer token factory to its caller, with its value
     */
    private ?BearerToken $createdBearerToken = null;

    /**
     * Error given by the bearer token factory to its caller
     */
    private ?Throwable $bearerTokenFactoryError = null;

    private function getBearerTokensOfTheUser(): ?BearerTokensAuth
    {
        Assert::assertInstanceOf(User::class, $this->user);

        foreach ($this->user->getAuthData() as $authData) {
            if ($authData instanceof BearerTokensAuth) {
                return $authData;
            }
        }

        return null;
    }

    private function getBearerTokenOfTheUser(string $name): BearerToken
    {
        $bearerToken = $this->getBearerTokensOfTheUser()?->getToken($name);
        Assert::assertInstanceOf(BearerToken::class, $bearerToken, "The user has no bearer token `{$name}`");

        return $bearerToken;
    }

    #[Given('a bearer token :name for the user with:')]
    public function aBearerTokenForTheUserWith(string $name, TableNode $values): void
    {
        $values = $values->getRowsHash();

        $bearerToken = new BearerToken(
            name: $name,
            tokenDigest: hash('sha256', $values['secret']),
            isExpired: 'true' === $values['expired'],
            createdAt: new DateTimeImmutable($values['createdAt']),
            expiresAt: new DateTimeImmutable($values['expiresAt']),
        );

        $bearerTokens = $this->getBearerTokensOfTheUser() ?? new BearerTokensAuth();
        $bearerTokens->addToken($bearerToken);
        $this->user->addAuthData($bearerTokens);
    }

    #[When('the bearer token factory creates the bearer token :name, expiring at :date')]
    public function theBearerTokenFactoryCreatesTheBearerToken(string $name, string $date): void
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $factory = $this->symfonyKernel->getContainer()->get(BearerTokenFactoryInterface::class);
        Assert::assertInstanceOf(BearerTokenFactoryInterface::class, $factory);

        $this->createdBearerToken = null;
        $this->bearerTokenFactoryError = null;

        /** @var Promise<BearerToken, mixed, mixed> $promise */
        $promise = new Promise(
            function (BearerToken $bearerToken): void {
                $this->createdBearerToken = $bearerToken;
            },
            function (#[SensitiveParameter] Throwable $error): void {
                $this->bearerTokenFactoryError = $error;
            },
        );

        $factory->create(
            $this->user,
            new BearerToken(name: $name, expiresAt: new DateTimeImmutable($date)),
            $promise,
        );
    }

    #[Then('the bearer token factory gives the value of the bearer token :name, remembered as :remembered')]
    public function theBearerTokenFactoryGivesTheValueOfTheBearerToken(string $name, string $remembered): void
    {
        Assert::assertNull($this->bearerTokenFactoryError);
        Assert::assertInstanceOf(BearerToken::class, $this->createdBearerToken);
        Assert::assertSame($name, $this->createdBearerToken->getName());
        Assert::assertMatchesRegularExpression('/^bt_[0-9a-f]{64}$/', $this->createdBearerToken->getToken());
        Assert::assertSame($this->createdBearerToken, $this->getBearerTokenOfTheUser($name));

        $this->remembered[$remembered] = $this->createdBearerToken->getToken();
    }

    #[Then('the bearer token factory refuses the bearer token with the error :message')]
    public function theBearerTokenFactoryRefusesTheBearerTokenWithTheError(string $message): void
    {
        Assert::assertNull($this->createdBearerToken);
        Assert::assertInstanceOf(Throwable::class, $this->bearerTokenFactoryError);
        Assert::assertSame($message, $this->bearerTokenFactoryError->getMessage());
        Assert::assertSame(400, $this->bearerTokenFactoryError->getCode());
    }

    #[Then('the user has no bearer token :name')]
    public function theUserHasNoBearerToken(string $name): void
    {
        Assert::assertNull($this->getBearerTokensOfTheUser()?->getToken($name));
    }

    #[Then('the bearer token :name of the user was created at :createdAt and expires at :expiresAt')]
    public function theBearerTokenOfTheUserWasCreatedAtAndExpiresAt(
        string $name,
        string $createdAt,
        string $expiresAt,
    ): void {
        $bearerToken = $this->getBearerTokenOfTheUser($name);

        Assert::assertSame($createdAt, $bearerToken->getCreatedAt()?->format('Y-m-d H:i:s'));
        Assert::assertSame($expiresAt, $bearerToken->getExpiresAt()?->format('Y-m-d H:i:s'));
        Assert::assertFalse($bearerToken->isExpired());
    }

    #[Then('only the digest of the secret :secret is stored with the bearer token :name of the user')]
    public function onlyTheDigestOfTheSecretIsStoredWithTheBearerTokenOfTheUser(string $secret, string $name): void
    {
        $bearerToken = $this->getBearerTokenOfTheUser($name);

        $secret = $this->replaceRememberedValues($secret);
        Assert::assertSame(hash('sha256', $secret), $bearerToken->getTokenDigest());
        Assert::assertNotEquals($secret, $bearerToken->getTokenDigest());
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

    #[Given('the bearer token :name of the user is marked as expired')]
    public function theBearerTokenOfTheUserIsMarkedAsExpired(string $name): void
    {
        $this->getBearerTokenOfTheUser($name)->setExpired(true);
    }

    #[Given('the bearer token :name of the user is removed')]
    public function theBearerTokenOfTheUserIsRemoved(string $name): void
    {
        $this->getBearerTokensOfTheUser()?->removeToken($name);
        $this->theUserHasNoBearerToken($name);
    }

    #[Given('the user is disabled')]
    public function theUserIsDisabled(): void
    {
        Assert::assertInstanceOf(User::class, $this->user);

        $this->user->setActive(false);
    }

    #[Given('the database is not available')]
    public function theDatabaseIsNotAvailable(): void
    {
        $this->getObjectRepository()->failWith(new RuntimeException('Connection refused', 500));
    }
}

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

namespace Teknoo\Tests\East\CommonBundle\Security;

use DateTimeImmutable;
use DomainException;
use EmptyIterator;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Security\ApiKeyTokenFactory;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeyTokenFactory::class)]
class ApiKeyTokenFactoryTest extends TestCase
{
    private function buildHasher(): UserPasswordHasherInterface
    {
        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed');

        return $hasher;
    }

    private function buildDatesService(): DatesService
    {
        return new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01 10:00:00'));
    }

    private function buildSuccessPromise(ApiKeyToken $apiKey): PromiseInterface
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($apiKey);
        $promise->expects($this->never())->method('fail');

        return $promise;
    }

    private function buildFailPromise(string $message, int $code): PromiseInterface
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())
            ->method('fail')
            ->with(
                $this->callback(
                    static fn (Throwable $error): bool => $error instanceof DomainException
                        && $message === $error->getMessage()
                        && $code === $error->getCode()
                )
            );

        return $promise;
    }

    public function testCreate(): void
    {
        $owner = new User()->setAuthData([new StoredPassword()]);
        $expiresAt = new DateTimeImmutable('2024-02-01 13:14:15');
        $apiKey = new ApiKeyToken(name: 'my-key', expiresAt: $expiresAt);

        $factory = new ApiKeyTokenFactory($this->buildDatesService(), $this->buildHasher(), 'ak_');

        $this->assertInstanceOf(
            ApiKeyTokenFactory::class,
            $factory->create($owner, $apiKey, $this->buildSuccessPromise($apiKey)),
        );

        $this->assertMatchesRegularExpression('/^ak_[0-9a-f]{64}$/', $apiKey->getToken());
        $this->assertSame('hashed', $apiKey->getTokenHash());
        $this->assertEquals(new DateTimeImmutable('2024-01-01 10:00:00'), $apiKey->getCreatedAt());
        $this->assertSame($expiresAt, $apiKey->getExpiresAt());
        $this->assertFalse($apiKey->isExpired());

        $apiKeys = $owner->getOneAuthData(ApiKeysAuth::class);
        $this->assertInstanceOf(ApiKeysAuth::class, $apiKeys);
        $this->assertSame($apiKey, $apiKeys->getToken('my-key'));
        $this->assertCount(2, [...$owner->getAuthData()]);
    }

    public function testCreateAddsTheKeyToTheCurrentApiKeysAuth(): void
    {
        $apiKeys = new class ([new ApiKeyToken('other-key')]) extends ApiKeysAuth {
        };
        $owner = new User()->setAuthData([$apiKeys]);
        $apiKey = new ApiKeyToken('my-key');

        $factory = new ApiKeyTokenFactory($this->buildDatesService(), $this->buildHasher());
        $factory->create($owner, $apiKey, $this->buildSuccessPromise($apiKey));

        $this->assertSame($apiKey, $apiKeys->getToken('my-key'));
        $this->assertCount(1, [...$owner->getAuthData()]);
    }

    public function testCreateReadsTheDateWithoutTheRealDateByDefault(): void
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), false)
            ->willReturn($datesService);

        $apiKey = new ApiKeyToken('my-key');
        $factory = new ApiKeyTokenFactory($datesService, $this->buildHasher());
        $factory->create(new User(), $apiKey, $this->buildSuccessPromise($apiKey));
    }

    public function testCreateReadsTheRealDate(): void
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturn($datesService);

        $apiKey = new ApiKeyToken('my-key');
        $factory = new ApiKeyTokenFactory($datesService, $this->buildHasher(), preferRealDate: true);
        $factory->create(new User(), $apiKey, $this->buildSuccessPromise($apiKey));
    }

    public function testCreateRefusesAnInvalidName(): void
    {
        $owner = new User();
        $apiKey = new ApiKeyToken('My key');

        $factory = new ApiKeyTokenFactory($this->buildDatesService(), $this->buildHasher());
        $factory->create(
            $owner,
            $apiKey,
            $this->buildFailPromise('teknoo.east.common.api_keys.form.name.regex_error', 400),
        );

        $this->assertSame('', $apiKey->getToken());
        $this->assertNull($owner->getOneAuthData(ApiKeysAuth::class));
    }

    public function testCreateRefusesANameAlreadyUsed(): void
    {
        $apiKeys = new ApiKeysAuth([$existing = new ApiKeyToken('my-key')]);
        $owner = new User()->setAuthData([$apiKeys]);
        $apiKey = new ApiKeyToken('my-key');

        $factory = new ApiKeyTokenFactory($this->buildDatesService(), $this->buildHasher());
        $factory->create(
            $owner,
            $apiKey,
            $this->buildFailPromise('teknoo.east.common.api_keys.error.already_exists', 400),
        );

        $this->assertSame('', $apiKey->getToken());
        $this->assertSame($existing, $apiKeys->getToken('my-key'));
    }

    public function testCreateWhenTheKeysOfTheOwnerAreNotAccessible(): void
    {
        //A list of keys without `ArrayAccess`: `ApiKeysAuth::addToken()` cannot add the new key
        $apiKeys = new ApiKeysAuth(
            new class () implements IteratorAggregate {
                public function getIterator(): Iterator
                {
                    return new EmptyIterator();
                }
            }
        );
        $owner = new User()->setAuthData([$apiKeys]);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())
            ->method('fail')
            ->with(
                $this->callback(
                    static fn (Throwable $error): bool => $error instanceof RuntimeException
                        && 'teknoo.east.common.api_keys.error.list_not_accessible' === $error->getMessage()
                )
            );

        $factory = new ApiKeyTokenFactory($this->buildDatesService(), $this->buildHasher());
        $factory->create($owner, new ApiKeyToken('my-key'), $promise);
    }
}

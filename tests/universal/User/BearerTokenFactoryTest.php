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

namespace Teknoo\Tests\East\Common\User;

use DateTimeImmutable;
use DomainException;
use EmptyIterator;
use Iterator;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\East\Common\Object\BearerToken;
use Teknoo\East\Common\Object\BearerTokensAuth;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\User\BearerTokenFactory;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

use function hash;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(BearerTokenFactory::class)]
class BearerTokenFactoryTest extends TestCase
{
    private function buildDatesService(): DatesService
    {
        return new DatesService()->setCurrentDate(new DateTimeImmutable('2024-01-01 10:00:00'));
    }

    private function buildSuccessPromise(BearerToken $bearerToken): PromiseInterface
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')->with($bearerToken);
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
        $bearerToken = new BearerToken(name: 'my-key', expiresAt: $expiresAt);

        $factory = new BearerTokenFactory($this->buildDatesService(), 'bt_');

        $this->assertInstanceOf(
            BearerTokenFactory::class,
            $factory->create($owner, $bearerToken, $this->buildSuccessPromise($bearerToken)),
        );

        $this->assertMatchesRegularExpression('/^bt_[0-9a-f]{64}$/', $bearerToken->getToken());
        $this->assertSame(hash('sha256', $bearerToken->getToken()), $bearerToken->getTokenDigest());
        $this->assertEquals(new DateTimeImmutable('2024-01-01 10:00:00'), $bearerToken->getCreatedAt());
        $this->assertSame($expiresAt, $bearerToken->getExpiresAt());
        $this->assertFalse($bearerToken->isExpired());

        $bearerTokens = $owner->getOneAuthData(BearerTokensAuth::class);
        $this->assertInstanceOf(BearerTokensAuth::class, $bearerTokens);
        $this->assertSame($bearerToken, $bearerTokens->getToken('my-key'));
        $this->assertCount(2, [...$owner->getAuthData()]);
    }

    public function testCreateWithoutPrefix(): void
    {
        $bearerToken = new BearerToken('my-key');

        $factory = new BearerTokenFactory($this->buildDatesService());
        $factory->create(new User(), $bearerToken, $this->buildSuccessPromise($bearerToken));

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $bearerToken->getToken());
    }

    public function testCreateAddsTheKeyToTheCurrentBearerTokensAuth(): void
    {
        $bearerTokens = new class ([new BearerToken('other-key')]) extends BearerTokensAuth {
        };
        $owner = new User()->setAuthData([$bearerTokens]);
        $bearerToken = new BearerToken('my-key');

        $factory = new BearerTokenFactory($this->buildDatesService());
        $factory->create($owner, $bearerToken, $this->buildSuccessPromise($bearerToken));

        $this->assertSame($bearerToken, $bearerTokens->getToken('my-key'));
        $this->assertCount(1, [...$owner->getAuthData()]);
    }

    public function testCreateReadsTheDateWithoutTheRealDateByDefault(): void
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), false)
            ->willReturn($datesService);

        $bearerToken = new BearerToken('my-key');
        $factory = new BearerTokenFactory($datesService);
        $factory->create(new User(), $bearerToken, $this->buildSuccessPromise($bearerToken));
    }

    public function testCreateReadsTheRealDate(): void
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturn($datesService);

        $bearerToken = new BearerToken('my-key');
        $factory = new BearerTokenFactory($datesService, preferRealDate: true);
        $factory->create(new User(), $bearerToken, $this->buildSuccessPromise($bearerToken));
    }

    public function testCreateRefusesAnInvalidName(): void
    {
        $owner = new User();
        $bearerToken = new BearerToken('My key');

        $factory = new BearerTokenFactory($this->buildDatesService());
        $factory->create(
            $owner,
            $bearerToken,
            $this->buildFailPromise('teknoo.east.common.bearer_tokens.error.invalid_name', 400),
        );

        $this->assertSame('', $bearerToken->getToken());
        $this->assertNull($owner->getOneAuthData(BearerTokensAuth::class));
    }

    public function testCreateRefusesANameAlreadyUsed(): void
    {
        $bearerTokens = new BearerTokensAuth([$existing = new BearerToken('my-key')]);
        $owner = new User()->setAuthData([$bearerTokens]);
        $bearerToken = new BearerToken('my-key');

        $factory = new BearerTokenFactory($this->buildDatesService());
        $factory->create(
            $owner,
            $bearerToken,
            $this->buildFailPromise('teknoo.east.common.bearer_tokens.error.already_exists', 400),
        );

        $this->assertSame('', $bearerToken->getToken());
        $this->assertSame($existing, $bearerTokens->getToken('my-key'));
    }

    public function testCreateWhenTheKeysOfTheOwnerAreNotAccessible(): void
    {
        //A list of keys without `ArrayAccess`: `BearerTokensAuth::addToken()` cannot add the new key
        $bearerTokens = new BearerTokensAuth(
            new class () implements IteratorAggregate {
                public function getIterator(): Iterator
                {
                    return new EmptyIterator();
                }
            }
        );
        $owner = new User()->setAuthData([$bearerTokens]);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->never())->method('success');
        $promise->expects($this->once())
            ->method('fail')
            ->with(
                $this->callback(
                    static fn (Throwable $error): bool => $error instanceof RuntimeException
                        && 'teknoo.east.common.bearer_tokens.error.list_not_accessible' === $error->getMessage()
                )
            );

        $factory = new BearerTokenFactory($this->buildDatesService());
        $factory->create($owner, new BearerToken('my-key'), $promise);
    }
}

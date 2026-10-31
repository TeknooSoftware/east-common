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

namespace Teknoo\Tests\East\CommonBundle\Security\Authenticator;

use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Teknoo\East\Common\Contracts\Query\QueryElementInterface;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ApiKeysAuth;
use Teknoo\East\Common\Object\ApiKeyToken;
use Teknoo\East\Common\Object\StoredPassword;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Query\User\UserByApiKeyDigestQuery;
use Teknoo\East\CommonBundle\Object\ApiKeysAuthUser;
use Teknoo\East\CommonBundle\Security\Authenticator\ApiKeyAuthenticator;
use Teknoo\East\Foundation\Time\DatesService;
use Teknoo\Recipe\Promise\PromiseInterface;

use function hash;
use function str_contains;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiKeyAuthenticator::class)]
class ApiKeyAuthenticatorTest extends TestCase
{
    private const string VALUE = 'ak_0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private function buildUser(?ApiKeyToken $apiKey = null): User
    {
        $apiKey ??= new ApiKeyToken(
            name: 'my-key',
            expiresAt: new DateTimeImmutable('2099-12-31 23:59:59'),
            tokenDigest: hash('sha256', self::VALUE),
            scope: 'inference',
        );

        return new User()
            ->setEmail('user@teknoo.software')
            ->setAuthData([new StoredPassword(), new ApiKeysAuth([new ApiKeyToken('other-key'), $apiKey])]);
    }

    private function buildLoader(?User $user, string $value = self::VALUE): UserLoader
    {
        $loader = $this->createMock(UserLoader::class);
        $loader->expects($this->once())
            ->method('fetch')
            ->with(new UserByApiKeyDigestQuery(hash('sha256', $value)), $this->isInstanceOf(PromiseInterface::class))
            ->willReturnCallback(
                static function (QueryElementInterface $query, PromiseInterface $promise) use ($user, $loader) {
                    if (null === $user) {
                        $promise->fail(new DomainException('teknoo.east.common.error.not_found', 404));
                    } else {
                        $promise->success($user);
                    }

                    return $loader;
                }
            );

        return $loader;
    }

    private function buildDatesService(): DatesService
    {
        return new DatesService()->setCurrentDate(new DateTimeImmutable('2050-06-01 12:00:00'));
    }

    /**
     * @param array<int, string> $acceptedScopes
     */
    private function buildAuthenticator(
        UserLoader $loader,
        ?string $secondHeader = null,
        array $acceptedScopes = [],
        ?DatesService $datesService = null,
        ?AuthenticationFailureHandlerInterface $failureHandler = null,
    ): ApiKeyAuthenticator {
        return new ApiKeyAuthenticator(
            loader: $loader,
            datesService: $datesService ?? $this->buildDatesService(),
            failureHandler: $failureHandler ?? $this->createStub(AuthenticationFailureHandlerInterface::class),
            secondHeader: $secondHeader,
            acceptedScopes: $acceptedScopes,
            preferRealDate: false,
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function buildRequest(array $headers): Request
    {
        $request = Request::create('https://foo.com/api');
        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    private function assertRefused(ApiKeyAuthenticator $authenticator, Request $request, string $message): void
    {
        try {
            $authenticator->authenticate($request);
        } catch (CustomUserMessageAuthenticationException $exception) {
            $this->assertSame($message, $exception->getMessageKey());
            $this->assertFalse(str_contains($exception->getMessage(), self::VALUE));

            return;
        }

        $this->fail('The request must be refused');
    }

    private function assertAuthenticated(Passport $passport, User $user, string $name, ?string $scope): void
    {
        $symfonyUser = $passport->getUser();
        $this->assertInstanceOf(ApiKeysAuthUser::class, $symfonyUser);
        $this->assertSame($user, $symfonyUser->getWrappedUser());
        $this->assertSame('user@teknoo.software', $symfonyUser->getUserIdentifier());
        $this->assertSame($name, $passport->getAttribute('api_key_name'));
        $this->assertSame($scope, $passport->getAttribute('api_key_scope'));
    }

    public function testSupportsEachRequest(): void
    {
        $authenticator = $this->buildAuthenticator($this->createStub(UserLoader::class));

        $this->assertTrue($authenticator->supports(Request::create('https://foo.com/api')));
    }

    public function testAuthenticateWithTheHeaderAuthorization(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user), 'x-api-key');

        $passport = $authenticator->authenticate($this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]));

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testAuthenticateWithTheSecondHeader(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user), 'x-api-key');

        $passport = $authenticator->authenticate($this->buildRequest(['x-api-key' => self::VALUE]));

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testAuthenticateWithTheSameValueInTheTwoHeaders(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user), 'x-api-key');

        $passport = $authenticator->authenticate(
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE, 'x-api-key' => self::VALUE])
        );

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testAuthenticateWithAnEmptySecondHeader(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user), 'x-api-key');

        $passport = $authenticator->authenticate(
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE, 'x-api-key' => ''])
        );

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testAuthenticateRefusesTwoDifferentValues(): void
    {
        $loader = $this->createMock(UserLoader::class);
        $loader->expects($this->never())->method('fetch');

        $this->assertRefused(
            $this->buildAuthenticator($loader, 'x-api-key'),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE, 'x-api-key' => 'ak_other']),
            'teknoo.east.common.api_keys.error.different_values',
        );
    }

    public function testAuthenticateIgnoresAnotherHeaderWithoutSecondHeader(): void
    {
        $loader = $this->createMock(UserLoader::class);
        $loader->expects($this->never())->method('fetch');

        $this->assertRefused(
            $this->buildAuthenticator($loader),
            $this->buildRequest(['x-api-key' => self::VALUE]),
            'teknoo.east.common.api_keys.error.missing',
        );
    }

    public function testAuthenticateRefusesARequestWithoutKey(): void
    {
        $loader = $this->createMock(UserLoader::class);
        $loader->expects($this->never())->method('fetch');
        $authenticator = $this->buildAuthenticator($loader, 'x-api-key');

        $this->assertRefused(
            $authenticator,
            $this->buildRequest([]),
            'teknoo.east.common.api_keys.error.missing',
        );

        $this->assertRefused(
            $authenticator,
            $this->buildRequest(['Authorization' => 'Bearer ', 'x-api-key' => ' ']),
            'teknoo.east.common.api_keys.error.missing',
        );

        $this->assertRefused(
            $authenticator,
            $this->buildRequest(['Authorization' => 'Basic ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.missing',
        );
    }

    public function testAuthenticateRefusesAnUnknownKey(): void
    {
        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader(null)),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.invalid',
        );
    }

    public function testAuthenticateRefusesAKeyWithoutDigest(): void
    {
        //A key created before East Common 4.9 has no digest : its user can not be found by the database, but the
        //authenticator checks also the key in the user
        $user = $this->buildUser(
            new ApiKeyToken(name: 'my-key', expiresAt: new DateTimeImmutable('2099-12-31 23:59:59')),
        );

        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader($user)),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.invalid',
        );
    }

    public function testAuthenticateRefusesAnExpiredKey(): void
    {
        $user = $this->buildUser(
            new ApiKeyToken(
                name: 'my-key',
                expiresAt: new DateTimeImmutable('2050-06-01 11:59:59'),
                tokenDigest: hash('sha256', self::VALUE),
            ),
        );

        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader($user)),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.invalid',
        );
    }

    public function testAuthenticateRefusesAKeyMarkedAsExpired(): void
    {
        $user = $this->buildUser(
            new ApiKeyToken(
                name: 'my-key',
                isExpired: true,
                expiresAt: new DateTimeImmutable('2099-12-31 23:59:59'),
                tokenDigest: hash('sha256', self::VALUE),
            ),
        );

        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader($user)),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.invalid',
        );
    }

    public function testAuthenticateRefusesTheKeyOfAnInactiveUser(): void
    {
        $user = $this->buildUser()->setActive(false);

        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader($user)),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.invalid',
        );
    }

    public function testAuthenticateRefusesAScopeThatTheFirewallDoesNotAccept(): void
    {
        $this->assertRefused(
            $this->buildAuthenticator($this->buildLoader($this->buildUser()), acceptedScopes: ['management']),
            $this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]),
            'teknoo.east.common.api_keys.error.scope_refused',
        );
    }

    public function testAuthenticateWithAnAcceptedScope(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user), acceptedScopes: ['inference']);

        $passport = $authenticator->authenticate($this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]));

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testAuthenticateAKeyWithoutScopeWithoutAcceptedScopes(): void
    {
        $user = $this->buildUser(
            new ApiKeyToken(
                name: 'my-key',
                expiresAt: new DateTimeImmutable('2099-12-31 23:59:59'),
                tokenDigest: hash('sha256', self::VALUE),
            ),
        );
        $authenticator = $this->buildAuthenticator($this->buildLoader($user));

        $passport = $authenticator->authenticate($this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]));

        $this->assertAuthenticated($passport, $user, 'my-key', null);
    }

    public function testAuthenticateReadsTheRealDateByDefault(): void
    {
        $datesService = $this->createMock(DatesService::class);
        $datesService->expects($this->once())
            ->method('passMeTheDate')
            ->with($this->isCallable(), true)
            ->willReturnCallback(
                static function (callable $setter) use ($datesService): DatesService {
                    $setter(new DateTimeImmutable('2050-06-01 12:00:00'));

                    return $datesService;
                }
            );

        $user = $this->buildUser();
        $authenticator = new ApiKeyAuthenticator(
            $this->buildLoader($user),
            $datesService,
            $this->createStub(AuthenticationFailureHandlerInterface::class),
        );

        $passport = $authenticator->authenticate($this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]));

        $this->assertAuthenticated($passport, $user, 'my-key', 'inference');
    }

    public function testCreateTokenGivesTheNameAndTheScopeOfTheKey(): void
    {
        $user = $this->buildUser();
        $authenticator = $this->buildAuthenticator($this->buildLoader($user));
        $passport = $authenticator->authenticate($this->buildRequest(['Authorization' => 'Bearer ' . self::VALUE]));

        $token = $authenticator->createToken($passport, 'api');

        $this->assertSame('my-key', $token->getAttribute('api_key_name'));
        $this->assertSame('inference', $token->getAttribute('api_key_scope'));
        $this->assertSame($passport->getUser(), $token->getUser());
    }

    public function testOnAuthenticationSuccess(): void
    {
        $authenticator = $this->buildAuthenticator($this->createStub(UserLoader::class));

        $this->assertNull(
            $authenticator->onAuthenticationSuccess(
                Request::create('https://foo.com/api'),
                $this->createStub(TokenInterface::class),
                'api',
            )
        );
    }

    public function testOnAuthenticationFailureCallsTheFailureHandler(): void
    {
        $request = Request::create('https://foo.com/api');
        $exception = new AuthenticationException();
        $response = new Response('', 401);

        $failureHandler = $this->createMock(AuthenticationFailureHandlerInterface::class);
        $failureHandler->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($request, $exception)
            ->willReturn($response);

        $authenticator = $this->buildAuthenticator(
            loader: $this->createStub(UserLoader::class),
            failureHandler: $failureHandler,
        );

        $this->assertSame($response, $authenticator->onAuthenticationFailure($request, $exception));
    }
}

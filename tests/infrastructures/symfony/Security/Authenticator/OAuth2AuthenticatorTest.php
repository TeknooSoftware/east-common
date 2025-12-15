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

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Teknoo\East\Common\Loader\UserLoader;
use Teknoo\East\Common\Object\ThirdPartyAuth;
use Teknoo\East\Common\Object\TOTPAuth;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface;
use Teknoo\East\CommonBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\GoogleAuthThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Object\TOTP\TOTPThirdPartyAuthenticatedUser;
use Teknoo\East\CommonBundle\Security\Authenticator\OAuth2Authenticator;
use Teknoo\East\CommonBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(OAuth2Authenticator::class)]
class OAuth2AuthenticatorTest extends TestCase
{
    private (ClientRegistry&Stub)|(ClientRegistry&MockObject)|null $clientRegistry = null;

    private (UserLoader&Stub)|(UserLoader&MockObject)|null $loader = null;

    private (SymfonyUserWriter&Stub)|(SymfonyUserWriter&MockObject)|null $userWriter = null;

    private (UserConverterInterface&Stub)|(UserConverterInterface&MockObject)|null $userConverter = null;

    public function getClientRegistry(bool $stub = false): (ClientRegistry&Stub)|(ClientRegistry&MockObject)
    {
        if (!$this->clientRegistry instanceof ClientRegistry) {
            if ($stub) {
                $this->clientRegistry = $this->createStub(ClientRegistry::class);
            } else {
                $this->clientRegistry = $this->createMock(ClientRegistry::class);
            }
        }

        return $this->clientRegistry;
    }

    public function getLoader(bool $stub = false): (UserLoader&Stub)|(UserLoader&MockObject)
    {
        if (!$this->loader instanceof UserLoader) {
            if ($stub) {
                $this->loader = $this->createStub(UserLoader::class);
            } else {
                $this->loader = $this->createMock(UserLoader::class);
            }
        }

        return $this->loader;
    }

    public function getSymfonyUserWriter(bool $stub = false): (SymfonyUserWriter&Stub)|(SymfonyUserWriter&MockObject)
    {
        if (!$this->userWriter instanceof SymfonyUserWriter) {
            if ($stub) {
                $this->userWriter = $this->createStub(SymfonyUserWriter::class);
            } else {
                $this->userWriter = $this->createMock(SymfonyUserWriter::class);
            }
        }

        return $this->userWriter;
    }

    public function getUserConverterInterface(bool $stub = false): (UserConverterInterface&Stub)|(UserConverterInterface&MockObject)
    {
        if (!$this->userConverter instanceof UserConverterInterface) {
            if ($stub) {
                $this->userConverter = $this->createStub(UserConverterInterface::class);
            } else {
                $this->userConverter = $this->createMock(UserConverterInterface::class);
            }
        }

        return $this->userConverter;
    }

    public function buildAuthenticator(): OAuth2Authenticator
    {
        return new OAuth2Authenticator(
            $this->getClientRegistry(true),
            $this->getLoader(true),
            $this->getSymfonyUserWriter(true),
            $this->getUserConverterInterface(true)
        );
    }

    public function testSupports(): void
    {
        $authenticator = $this->buildAuthenticator();

        $this->assertFalse(
            $authenticator->supports(new Request())
        );

        $this->assertFalse(
            $authenticator->supports(new Request(['_oauth_client_key' => 'foo']))
        );

        $this->assertFalse(
            $authenticator->supports(new Request([], ['_oauth_client_key' => 'foo']))
        );

        $this->assertTrue(
            $authenticator->supports(new Request([], [], ['_oauth_client_key' => 'bar']))
        );
    }

    public function testRegisterTokenWithoutThirdParty(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAuthData')->willReturn([]);
        $user->expects($this->once())
            ->method('addAuthData')
            ->with(
                new ThirdPartyAuth()->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x): bool => $x instanceof ThirdPartyAuthenticatedUser
            ))
        ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithAnotherProviderThirdParty(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAuthData')->willReturn([
            new ThirdPartyAuth()->setProtocol('oauth2')
                ->setProvider('provider2')
                ->setToken('token')
        ]);
        $user->expects($this->once())
            ->method('addAuthData')
            ->with(
                new ThirdPartyAuth()->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x): bool => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdParty(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAuthData')->willReturn([
            new ThirdPartyAuth()->setProtocol('oauth2')
                ->setProvider('provider')
                ->setToken('token')
        ]);
        $user->expects($this->never())
            ->method('addAuthData');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x): bool => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdPartyWith2FAGoogle(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAuthData')->willReturn([
            new ThirdPartyAuth()->setProtocol('oauth2')
                ->setProvider('provider')
                ->setToken('token'),
            new TOTPAuth(
                provider: TOTPAuth::PROVIDER_GOOGLE_AUTHENTICATOR,
                algorithm: 'sha1',
                period: 30,
                digits: 6,
                enabled: true,
            ),
        ]);
        $user->expects($this->never())
            ->method('addAuthData');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x): bool => $x instanceof GoogleAuthThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdPartyWith2FACommon(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getAuthData')->willReturn([
            new ThirdPartyAuth()->setProtocol('oauth2')
                ->setProvider('provider')
                ->setToken('token'),
            new TOTPAuth(
                provider: 'aTotp',
                algorithm: 'sha1',
                period: 30,
                digits: 6,
                enabled: true,
            ),
        ]);
        $user->expects($this->never())
            ->method('addAuthData');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x): bool => $x instanceof TOTPThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testAuthenticateUserFound(): void
    {
        $request = new Request([], [], ['_oauth_client_key' => 'foo']);

        $token = $this->createStub(AccessToken::class);
        $token->method('getToken')->willReturn('foo');

        $client = $this->createStub(OAuth2ClientInterface::class);
        $client->method('getAccessToken')->willReturn($token);
        $client->method('fetchUserFromToken')->willReturn(
            $this->createStub(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface(true)
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader(true)
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                    $promise->success(new User());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $user = $passport->getUser();
        $this->assertInstanceOf(
            ThirdPartyAuthenticatedUser::class,
            $user
        );
    }

    public function testAuthenticateUserNotFound(): void
    {
        $request = new Request([], [], ['_oauth_client_key' => 'foo']);

        $token = $this->createStub(AccessToken::class);
        $token->method('getToken')->willReturn('foo');

        $client = $this->createStub(OAuth2ClientInterface::class);
        $client->method('getAccessToken')->willReturn($token);
        $client->method('fetchUserFromToken')->willReturn(
            $this->createStub(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface(true)
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface(true)
            ->method('convertToUser')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->success(new User());

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader(true)
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                    $promise->fail(new \DomainException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $user = $passport->getUser();
        $this->assertInstanceOf(
            ThirdPartyAuthenticatedUser::class,
            $user
        );
    }

    public function testAuthenticateUserOtherError(): void
    {
        $request = new Request([], [], ['_oauth_client_key' => 'foo']);

        $token = $this->createStub(AccessToken::class);
        $token->method('getToken')->willReturn('foo');

        $client = $this->createStub(OAuth2ClientInterface::class);
        $client->method('getAccessToken')->willReturn($token);
        $client->method('fetchUserFromToken')->willReturn(
            $this->createStub(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects($this->never())
            ->method('convertToUser');

        $this->getLoader(true)
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                    $promise->fail(new \RuntimeException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testAuthenticateUserOtherError2(): void
    {
        $request = new Request([], [], ['_oauth_client_key' => 'foo']);

        $token = $this->createStub(AccessToken::class);
        $token->method('getToken')->willReturn('foo');

        $client = $this->createStub(OAuth2ClientInterface::class);
        $client->method('getAccessToken')->willReturn($token);
        $client->method('fetchUserFromToken')->willReturn(
            $this->createStub(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects($this->never())
            ->method('convertToUser');

        $this->getLoader(true)
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise): \Teknoo\East\Common\Loader\UserLoader {
                    $promise->fail(new \RuntimeException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testAuthenticateUserErrorInEmail(): void
    {
        $request = new Request([], [], ['_oauth_client_key' => 'foo']);

        $token = $this->createStub(AccessToken::class);
        $token->method('getToken')->willReturn('foo');

        $client = $this->createStub(OAuth2ClientInterface::class);
        $client->method('getAccessToken')->willReturn($token);
        $client->method('fetchUserFromToken')->willReturn(
            $this->createStub(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry(true)
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface(true)
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise): \Teknoo\East\CommonBundle\Contracts\Security\Authenticator\UserConverterInterface {
                    $promise->fail(new \RuntimeException());

                    return $this->getUserConverterInterface();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testOnAuthenticationFailure(): void
    {
        $this->assertInstanceOf(
            Response::class,
            $this->buildAuthenticator()
                ->onAuthenticationFailure(
                    $this->createStub(Request::class),
                    $this->createStub(AuthenticationException::class)
                )
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $this->assertNotInstanceOf(
            \Symfony\Component\HttpFoundation\Response::class,
            $this->buildAuthenticator()
                ->onAuthenticationSuccess(
                    $this->createStub(Request::class),
                    $this->createStub(TokenInterface::class),
                    'foo'
                )
        );
    }
}

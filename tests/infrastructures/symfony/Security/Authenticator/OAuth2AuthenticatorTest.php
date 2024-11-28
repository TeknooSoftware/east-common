<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(OAuth2Authenticator::class)]
class OAuth2AuthenticatorTest extends TestCase
{
    private ?ClientRegistry $clientRegistry = null;

    private ?UserLoader $loader = null;

    private ?SymfonyUserWriter $userWriter = null;

    private ?UserConverterInterface $userConverter = null;

    /**
     * @return UserLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getClientRegistry(): ClientRegistry
    {
        if (!$this->clientRegistry instanceof ClientRegistry) {
            $this->clientRegistry = $this->createMock(ClientRegistry::class);
        }

        return $this->clientRegistry;
    }

    /**
     * @return UserLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getLoader(): UserLoader
    {
        if (!$this->loader instanceof UserLoader) {
            $this->loader = $this->createMock(UserLoader::class);
        }

        return $this->loader;
    }

    /**
     * @return SymfonyUserWriter|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getSymfonyUserWriter(): SymfonyUserWriter
    {
        if (!$this->userWriter instanceof SymfonyUserWriter) {
            $this->userWriter = $this->createMock(SymfonyUserWriter::class);
        }

        return $this->userWriter;
    }

    /**
     * @return UserLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getUserConverterInterface(): UserConverterInterface
    {
        if (!$this->userConverter instanceof UserConverterInterface) {
            $this->userConverter = $this->createMock(UserConverterInterface::class);
        }

        return $this->userConverter;
    }

    public function buildAuthenticator(): OAuth2Authenticator
    {
        return new OAuth2Authenticator(
            $this->getClientRegistry(),
            $this->getLoader(),
            $this->getSymfonyUserWriter(),
            $this->getUserConverterInterface()
        );
    }

    public function testSupports()
    {
        $authenticator = $this->buildAuthenticator();

        self::assertFalse(
            $authenticator->supports(new Request())
        );

        self::assertFalse(
            $authenticator->supports(new Request(['_oauth_client_key' => 'foo']))
        );

        self::assertFalse(
            $authenticator->supports(new Request([], ['_oauth_client_key' => 'foo']))
        );

        self::assertTrue(
            $authenticator->supports(new Request([], [], ['_oauth_client_key' => 'bar']))
        );
    }

    public function testRegisterTokenWithoutThirdParty()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getAuthData')->willReturn([]);
        $user->expects($this->once())
            ->method('addAuthData')
            ->with(
                (new ThirdPartyAuth())->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
        ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithAnotherProviderThirdParty()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
                ->setProvider('provider2')
                ->setToken('token')
        ]);
        $user->expects($this->once())
            ->method('addAuthData')
            ->with(
                (new ThirdPartyAuth())->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdParty()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
                ->setProvider('provider')
                ->setToken('token')
        ]);
        $user->expects($this->never())
            ->method('addAuthData');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdPartyWith2FAGoogle()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
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
                fn ($x) => $x instanceof GoogleAuthThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdPartyWith2FACommon()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
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
                fn ($x) => $x instanceof TOTPThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testAuthenticateUserFound()
    {
        $request = new Request([], [], ['_oauth_client_key'=>'foo']);

        $token = $this->createMock(AccessToken::class);
        $token->expects($this->any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects($this->any())->method('getAccessToken')->willReturn($token);
        $client->expects($this->any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader()
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->success(new User());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $user = $passport->getUser();
        self::assertInstanceOf(
            ThirdPartyAuthenticatedUser::class,
            $user
        );
    }

    public function testAuthenticateUserNotFound()
    {
        $request = new Request([], [], ['_oauth_client_key'=>'foo']);

        $token = $this->createMock(AccessToken::class);
        $token->expects($this->any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects($this->any())->method('getAccessToken')->willReturn($token);
        $client->expects($this->any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('convertToUser')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success(new User());

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader()
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new \DomainException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $user = $passport->getUser();
        self::assertInstanceOf(
            ThirdPartyAuthenticatedUser::class,
            $user
        );
    }

    public function testAuthenticateUserOtherError()
    {
        $request = new Request([], [], ['_oauth_client_key'=>'foo']);

        $token = $this->createMock(AccessToken::class);
        $token->expects($this->any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects($this->any())->method('getAccessToken')->willReturn($token);
        $client->expects($this->any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects($this->never())
            ->method('convertToUser');

        $this->getLoader()
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new \RuntimeException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testAuthenticateUserOtherError2()
    {
        $request = new Request([], [], ['_oauth_client_key'=>'foo']);

        $token = $this->createMock(AccessToken::class);
        $token->expects($this->any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects($this->any())->method('getAccessToken')->willReturn($token);
        $client->expects($this->any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects($this->never())
            ->method('convertToUser');

        $this->getLoader()
            ->expects($this->any())
            ->method('fetch')
            ->willReturnCallback(
                function ($query, PromiseInterface $promise) {
                    $promise->fail(new \RuntimeException());

                    return $this->getLoader();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testAuthenticateUserErrorInEmail()
    {
        $request = new Request([], [], ['_oauth_client_key'=>'foo']);

        $token = $this->createMock(AccessToken::class);
        $token->expects($this->any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects($this->any())->method('getAccessToken')->willReturn($token);
        $client->expects($this->any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects($this->any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects($this->any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->fail(new \RuntimeException());

                    return $this->getUserConverterInterface();
                }
            );

        $passport = $this->buildAuthenticator()->authenticate($request);

        self::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $this->expectException(\RuntimeException::class);
        $passport->getUser();
    }

    public function testOnAuthenticationFailure()
    {
        self::assertInstanceOf(
            Response::class,
            $this->buildAuthenticator()
                ->onAuthenticationFailure(
                    $this->createMock(Request::class),
                    $this->createMock(AuthenticationException::class)
                )
        );
    }

    public function testOnAuthenticationSuccess()
    {
        self::assertNull(
            $this->buildAuthenticator()
                ->onAuthenticationSuccess(
                    $this->createMock(Request::class),
                    $this->createMock(TokenInterface::class),
                    'foo'
                )
        );
    }
}

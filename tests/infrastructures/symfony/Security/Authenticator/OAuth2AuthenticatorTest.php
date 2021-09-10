<?php

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Security\Authenticator;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\Website\Object\ThirdPartyAuth;
use Teknoo\East\Website\Object\User;
use Teknoo\East\WebsiteBundle\Contracts\Security\Authenticator\UserConverterInterface;
use Teknoo\East\WebsiteBundle\Object\ThirdPartyAuthenticatedUser;
use Teknoo\East\WebsiteBundle\Security\Authenticator\OAuth2Authenticator;
use Teknoo\East\WebsiteBundle\Writer\SymfonyUserWriter;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Security\Authenticator\OAuth2Authenticator
 */
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

    public function testRegisterTokenWithoutThirdPart()
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())->method('getAuthData')->willReturn([]);
        $user->expects(self::once())
            ->method('addAuthData')
            ->with(
                (new ThirdPartyAuth())->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
        ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects(self::once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithAnotgerProviderThirdPart()
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
                ->setProvider('provider2')
                ->setToken('token')
        ]);
        $user->expects(self::once())
            ->method('addAuthData')
            ->with(
                (new ThirdPartyAuth())->setProtocol('oauth2')
                    ->setProvider('provider')
            )->willReturnSelf();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects(self::once())
            ->method('save')
            ->willReturnSelf();

        self::assertInstanceOf(
            OAuth2Authenticator::class,
            $this->buildAuthenticator()->registerToken($user, 'provider', 'token', $promise)
        );
    }

    public function testRegisterTokenWithThirdPart()
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())->method('getAuthData')->willReturn([
            (new ThirdPartyAuth())->setProtocol('oauth2')
                ->setProvider('provider')
                ->setToken('token')
        ]);
        $user->expects(self::never())
            ->method('addAuthData');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects(self::once())->method('success')
            ->with($this->callback(
                fn ($x) => $x instanceof ThirdPartyAuthenticatedUser
            ))
            ->willReturnSelf();

        $this->getSymfonyUserWriter()
            ->expects(self::once())
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
        $token->expects(self::any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects(self::any())->method('getAccessToken')->willReturn($token);
        $client->expects(self::any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects(self::any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects(self::any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader()
            ->expects(self::any())
            ->method('query')
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
        $token->expects(self::any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects(self::any())->method('getAccessToken')->willReturn($token);
        $client->expects(self::any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects(self::any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects(self::any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects(self::any())
            ->method('convertToUser')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success(new User());

                    return $this->getUserConverterInterface();
                }
            );

        $this->getLoader()
            ->expects(self::any())
            ->method('query')
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
        $token->expects(self::any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects(self::any())->method('getAccessToken')->willReturn($token);
        $client->expects(self::any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects(self::any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects(self::any())
            ->method('extractEmail')
            ->willReturnCallback(
                function (ResourceOwnerInterface $owner, PromiseInterface $promise) {
                    $promise->success('foo@bar');

                    return $this->getUserConverterInterface();
                }
            );

        $this->getUserConverterInterface()
            ->expects(self::never())
            ->method('convertToUser');

        $this->getLoader()
            ->expects(self::any())
            ->method('query')
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
        $token->expects(self::any())->method('getToken')->willReturn('foo');

        $client = $this->createMock(OAuth2ClientInterface::class);
        $client->expects(self::any())->method('getAccessToken')->willReturn($token);
        $client->expects(self::any())->method('fetchUserFromToken')->willReturn(
            $this->createMock(ResourceOwnerInterface::class)
        );

        $this->getClientRegistry()
            ->expects(self::any())
            ->method('getClient')
            ->willReturn($client);

        $this->getUserConverterInterface()
            ->expects(self::any())
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
